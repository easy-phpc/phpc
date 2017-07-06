<?php

// PHP Compiler by Serge Igitov (Dagdamor), Version 2.5.2, Copyright 2010
// Released under the LGPL License (www.gnu.org/copyleft/lesser.html)

define("ColumnAttributesCount",5);

define("ColumnAttributeNull",1);
define("ColumnAttributeBinary",2);
define("ColumnAttributeUnsigned",4);
define("ColumnAttributeZerofill",8);
define("ColumnAttributeCounter",16);

/******************************************************************************/

class Database
{
  var $type="mysql";
  var $title="MySQL";
  var $connection;
  var $database;
  var $debug;

  function Database()
  {
    if(!function_exists("mysqli_connect")) fatalError("fatal_function","mysqli_connect");
    $host=DatabaseHost; $port=null;
    if(strpos($host,":")) list($host,$port)=explode(":",$host);
    $this->connection=@mysqli_connect($host,DatabaseUser,DatabasePass,DatabaseName,$port) or fatalError("fatal_connection");
    $this->database=DatabaseName;
    $queries=array("SET SESSION sql_mode=''");
    if(defined("DatabaseCharset")) {
      $queries[]="SET NAMES ".DatabaseCharset;
      if(defined("PhpcAdminPanel")) $queries[]="ALTER DATABASE CHARACTER SET ".DatabaseCharset;
    }
    $queries=array_merge($queries,explodeSmart(";",DatabaseStartupQueries));
    mysqli_multi_query($this->connection,implode(";",$queries));
    while(mysqli_more_results($this->connection))
      { mysqli_store_result($this->connection); mysqli_next_result($this->connection); }
    $queries=DatabaseQueryLogEnabled?array():0;
    $this->debug=array("queries"=>$queries,"started"=>phpcmicrotime());
  }

  function internalNormalizeName($name)
  {
    static $cache;
    if(!isset($cache)) $cache=$this->getReservedWords();
    return in_array(phpcstrtoupper($name),$cache)?"`$name`":$name;
  }

  function internalNormalizeValue($value, $strict=true)
  {
    if(is_null($value)) return "NULL";
    if(is_bool($value)) return (int)$value;
    if(!$strict && isTrueInteger($value)) $value=(int)$value;
    if(is_int($value) || is_float($value)) return $value;
    return slashes((string)$value);
  }

  function internalTranslateAttributes($attrs, $default="")
  {
    $result=array();
    if($attrs&ColumnAttributeBinary) $result[]="BINARY";
    if($attrs&ColumnAttributeUnsigned) $result[]="UNSIGNED";
    if($attrs&ColumnAttributeZerofill) $result[]="ZEROFILL";
    $result[]=($attrs&ColumnAttributeNull)?"NULL":"NOT NULL";
    if($default!="") $result[]="DEFAULT ".slashes($default);
    if($attrs&ColumnAttributeCounter) $result[]="AUTO_INCREMENT";
    return implode(" ",$result);
  }

  function internalTranslateTableType($type)
  {
    $types=array(
      "BERKELEYDB"=>"BDB",
      "HEAP"=>"MEMORY",
      "INNODB"=>"InnoDB",
      "MRG_MYISAM"=>"MERGE",
      "MYISAM"=>"MyISAM");
    $type=phpcstrtoupper($type);
    return ifset($types[$type],$type);
  }

  function internalProcessResource($resource, $onlyfirst, $assoc)
  {
    if(!is_object($resource) && !is_resource($resource)) return false;
    $result=array();
    if($assoc===true)
      while($line=mysqli_fetch_assoc($resource)) $result[]=$line;
    else if(is_int($assoc))
      while($line=mysqli_fetch_row($resource)) $result[]=$line[$assoc];
    else if(is_string($assoc) && $assoc!="")
      while($line=mysqli_fetch_assoc($resource)) $result[]=$line[$assoc];
    else if(is_array($assoc))
      while($line=mysqli_fetch_assoc($resource)) $result[$line[$assoc[0]]]=$line[$assoc[1]];
    else if($assoc)
      while($line=mysqli_fetch_assoc($resource)) $result[]=$line;
      else while($line=mysqli_fetch_row($resource)) $result[]=$line;
    mysqli_free_result($resource);
    return $onlyfirst?(array_key_exists(0,$result)?$result[0]:false):$result;
  }

  function changeDatabase($name=DatabaseName)
  {
    @mysqli_select_db($this->connection,$name) or fatalError("fatal_connection");
    $this->database=$name;
  }

  function getDebugInformation()
  {
    $this->debug["finished"]=phpcmicrotime();
    $this->debug["elapsed"]=$this->debug["finished"]-$this->debug["started"];
    return $this->debug;
  }

  function customQuery($query, $onlyfirst=false, $assoc=true)
  {
    $resource=mysqli_query($this->connection,$query);
    if(!$resource) fatalError("fatal_wrongquery",array(),mysqli_error($this->connection),$query);
    if(DatabaseQueryLogEnabled)
      $this->debug["queries"][]=$query; else $this->debug["queries"]++;
    return $this->internalProcessResource($resource,$onlyfirst,$assoc);
  }

  function customQuerySilent($query, &$error, $onlyfirst=false, $assoc=true)
  {
    $error=false;
    $resource=mysqli_query($this->connection,$query);
    if(!$resource) { $error=mysqli_error($this->connection); return false; }
    if(DatabaseQueryLogEnabled)
      $this->debug["queries"][]=$query; else $this->debug["queries"]++;
    return $this->internalProcessResource($resource,$onlyfirst,$assoc);
  }

  function customQueryBoolean($query)
  {
    $result=(bool)mysqli_query($this->connection,$query);
    if(DatabaseQueryLogEnabled)
      $this->debug["queries"][]=$query; else $this->debug["queries"]++;
    return $result;
  }

  function addColumn($table, $column, $type, $attrs=0, $position=false, $default="")
  {
    $attrs=$this->internalTranslateAttributes($attrs,$default);
    $query="ALTER TABLE $table ADD $column $type $attrs";
    if($position!==false) {
      $fields=$this->getFieldsList($table);
      if(!isTrueInteger($position)) $position=array_search($position,$fields)+1;
      $position=max(min($position,count($fields)),0);
      $query.=$position?" AFTER ".$fields[$position-1]:" FIRST";
    }
    return $this->customQueryBoolean($query);
  }

  function addIndex($table, $name, $unique, $fields)
  {
    if(!is_array($fields)) $fields=array($fields);
    foreach($fields as $index=>$field)
      if(!is_array($field)) $fields[$index]=array("name"=>$field,"size"=>0);
    if($name=="PRIMARY") $name="PRIMARY KEY";
      else $name=($unique?"UNIQUE":"INDEX")." $name";
    $keystack=array();
    foreach($fields as $field)
      $keystack[]=$field["name"].($field["size"]?"($field[size])":"");
    $keystack=implode(",",$keystack);
    $query="ALTER TABLE $table ADD $name ($keystack)";
    return $this->customQueryBoolean($query);
  }

  function addLine($table, $values, $replace=false)
  {
    foreach($values as $field=>$value)
      $values[$field]=$this->internalNormalizeValue($value);
    $method=$replace?"REPLACE":"INSERT";
    $fields=implode(",",array_keys($values));
    $values=implode(",",$values);
    $query="$method INTO $table ($fields) VALUES ($values)";
    return $this->customQueryBoolean($query);
  }

  function addLineStrict($table, $values, $replace=false)
  {
    foreach($values as $field=>$value)
      $values[$field]=$this->internalNormalizeValue($value);
    $method=$replace?"REPLACE":"INSERT";
    $fields=implode(",",array_keys($values));
    $values=implode(",",$values);
    $query="$method INTO $table ($fields) VALUES ($values)";
    $this->customQuery($query);
    return true;
  }

  function modifyColumn($table, $column, $name, $type, $attrs=0, $default="")
  {
    $attrs=$this->internalTranslateAttributes($attrs,$default);
    $query="ALTER TABLE $table CHANGE $column $name $type $attrs";
    return $this->customQueryBoolean($query);
  }

  function modifyField($table, $field, $value, $conditions="")
  {
    return $this->modifyLine($table,array($field=>$value),$conditions);
  }

  function incrementField($table, $field, $conditions="", $delta=1)
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $delta=(int)$delta;
    $query="UPDATE $table SET $field=$field+$delta$conditions LIMIT 1";
    return $this->customQueryBoolean($query);
  }

  function decrementField($table, $field, $conditions="", $delta=1)
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $delta=(int)$delta;
    $query="UPDATE $table SET $field=$field-$delta$conditions LIMIT 1";
    return $this->customQueryBoolean($query);
  }

  function modifyLine($table, $values, $conditions="")
  {
    if(!count($values)) return false;
    $update="";
    foreach($values as $field=>$value)
      $update.=", $field=".$this->internalNormalizeValue($value);
    $update=substr($update,2);
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="UPDATE $table SET $update$conditions LIMIT 1";
    return $this->customQueryBoolean($query);
  }

  function modifyLines($table, $values, $conditions="")
  {
    if(!count($values)) return;
    $update="";
    foreach($values as $field=>$value)
      $update.=", $field=".$this->internalNormalizeValue($value);
    $update=substr($update,2);
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="UPDATE $table SET $update$conditions";
    $this->customQueryBoolean($query);
  }

  function deleteColumn($table, $column)
  {
    $query="ALTER TABLE $table DROP $column";
    return $this->customQueryBoolean($query);
  }

  function deleteIndex($table, $name)
  {
    $name=$name=="PRIMARY"?"PRIMARY KEY":"INDEX $name";
    $query="ALTER TABLE $table DROP $name";
    return $this->customQueryBoolean($query);
  }

  function deleteLine($table, $conditions="")
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="DELETE FROM $table$conditions LIMIT 1";
    return $this->customQueryBoolean($query);
  }

  function deleteLines($table, $conditions="")
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="DELETE FROM $table$conditions";
    $this->customQueryBoolean($query);
  }

  function clearTable($table)
  {
    $query="TRUNCATE TABLE $table";
    $this->customQueryBoolean($query);
  }

  function deleteTable($table)
  {
    $query="DROP TABLE $table";
    $this->customQueryBoolean($query);
  }

  function getVersion()
  {
    return mysqli_get_server_info($this->connection);
  }

  function getTableTypes()
  {
    $types="MyISAM,MEMORY,MERGE,BDB,InnoDB";
    return explode(",",$types);
  }

  function getFieldTypes()
  {
    $types=
      "TINYINT,SMALLINT,MEDIUMINT,INT,BIGINT,FLOAT,DOUBLE,DECIMAL,CHAR,".
      "VARCHAR,TINYTEXT,TEXT,MEDIUMTEXT,LONGTEXT,TINYBLOB,BLOB,MEDIUMBLOB,".
      "LONGBLOB,DATE,TIME,DATETIME,YEAR,TIMESTAMP,ENUM,SET";
    return explode(",",$types);
  }

  function getNumericFieldTypes()
  {
    $types=
      "TINYINT,SMALLINT,MEDIUMINT,INT,BIGINT,FLOAT,DOUBLE,DECIMAL,DATE,TIME,".
      "DATETIME,YEAR,TIMESTAMP,ENUM,SET";
    return explode(",",$types);
  }

  function getReservedWords()
  {
    $words=
      "ACCESSIBLE,ACTION,ADD,AGAINST,ALL,ALTER,ANALYZE,AND,AS,ASC,ASENSITIVE,".
      "AUTO_INCREMENT,BEFORE,BETWEEN,BIGINT,BINARY,BIT,BLOB,BOTH,BY,CALL,".
      "CASCADE,CASE,CHANGE,CHAR,CHARACTER,CHECK,COLLATE,COLUMN,CONDITION,".
      "CONSTRAINT,CONTINUE,CONVERT,COUNT,CREATE,CROSS,CURRENT_DATE,".
      "CURRENT_TIME,CURRENT_TIMESTAMP,CURRENT_USER,CURSOR,DATABASE,DATABASES,".
      "DATE,DAY_HOUR,DAY_MICROSECOND,DAY_MINUTE,DAY_SECOND,DEC,DECIMAL,".
      "DECLARE,DEFAULT,DELAYED,DELETE,DESC,DESCRIBE,DETERMINISTIC,DISTINCT,".
      "DISTINCTROW,DIV,DOUBLE,DROP,DUAL,EACH,ELSE,ELSEIF,ENCLOSED,ENUM,".
      "ESCAPED,EXISTS,EXIT,EXPLAIN,FALSE,FETCH,FLOAT,FLOAT4,FLOAT8,FOR,FORCE,".
      "FOREIGN,FROM,FULLTEXT,GRANT,GROUP,HAVING,HIGH_PRIORITY,".
      "HOUR_MICROSECOND,HOUR_MINUTE,HOUR_SECOND,IF,IGNORE,IN,INDEX,INFILE,".
      "INNER,INOUT,INSENSITIVE,INSERT,INT,INT1,INT2,INT3,INT4,INT8,INTEGER,".
      "INTERVAL,INTO,IS,ITERATE,JOIN,KEY,KEYS,KILL,LEADING,LEAVE,LEFT,LIKE,".
      "LIMIT,LINEAR,LINES,LOAD,LOCALTIME,LOCALTIMESTAMP,LOCK,LONG,LONGBLOB,".
      "LONGTEXT,LOOP,LOW_PRIORITY,MATCH,MEDIUMBLOB,MEDIUMINT,MEDIUMTEXT,".
      "MIDDLEINT,MINUTE_MICROSECOND,MINUTE_SECOND,MOD,MODIFIES,NATURAL,NOT,".
      "NO_WRITE_TO_BINLOG,NULL,NUMERIC,ON,OPTIMIZE,OPTION,OPTIONALLY,OR,".
      "ORDER,OUT,OUTER,OUTFILE,PRECISION,PRIMARY,PROCEDURE,PURGE,RANGE,READ,".
      "READS,READ_ONLY,READ_WRITE,REAL,REFERENCES,REGEXP,RELEASE,RENAME,".
      "REPEAT,REPLACE,REQUIRE,RESTRICT,RETURN,REVOKE,RIGHT,RLIKE,SCHEMA,".
      "SCHEMAS,SECOND_MICROSECOND,SELECT,SENSITIVE,SEPARATOR,SET,SHOW,".
      "SMALLINT,SPATIAL,SPECIFIC,SQL,SQLEXCEPTION,SQLSTATE,SQLWARNING,".
      "SQL_BIG_RESULT,SQL_CALC_FOUND_ROWS,SQL_SMALL_RESULT,SSL,STARTING,".
      "STRAIGHT_JOIN,TABLE,TERMINATED,TEXT,THEN,TIME,TIMESTAMP,TINYBLOB,".
      "TINYINT,TINYTEXT,TO,TRAILING,TRIGGER,TRUE,UNDO,UNION,UNIQUE,UNLOCK,".
      "UNSIGNED,UPDATE,USAGE,USE,USING,UTC_DATE,UTC_TIME,UTC_TIMESTAMP,".
      "VALUES,VARBINARY,VARCHAR,VARCHARACTER,VARYING,WHEN,WHERE,WHILE,WITH,".
      "WRITE,X509,XOR,YEAR_MONTH,ZEROFILL";
    return explode(",",$words);
  }

  function getSupportedAttributes()
  {
    return ColumnAttributeNull+ColumnAttributeBinary+ColumnAttributeUnsigned+
      ColumnAttributeZerofill+ColumnAttributeCounter;
  }

  function getColumnType($meaning)
  {
    $types=array(
      "key"=>"INT",
      "int"=>"INT",
      "float"=>"DOUBLE",
      "double"=>"DOUBLE",
      "input"=>"TINYTEXT",
      "inputorder"=>"INT",
      "password"=>"TINYTEXT",
      "date"=>"DATE",
      "datetime"=>"INT",
      "file"=>"LONGBLOB",
      "chooser"=>"TINYTEXT",
      "intchooser"=>"INT",
      "floatchooser"=>"DOUBLE",
      "textchooser"=>"TINYTEXT",
      "groupchooser"=>"TINYTEXT",
      "intgroupchooser"=>"INT",
      "floatgroupchooser"=>"DOUBLE",
      "textgroupchooser"=>"TINYTEXT",
      "yesno"=>"TINYINT(1)",
      "yesnoany"=>"TINYINT(1)",
      "yesnonull"=>"TINYINT(1)",
      "approval"=>"INT");
    return ifset($types[$meaning],"LONGTEXT");
  }

  function getColumnMeaning($type)
  {
    if($type=="DATE") return "date";
    if($type=="TINYINT(1)") return "yesno";
    if(preg_match("{INT\b}",$type)) return "int";
    if(preg_match("{\b(FLOAT|DOUBLE|DECIMAL)\b}",$type)) return "float";
    if(preg_match("{\b(TEXT|MEDIUMTEXT|LONGTEXT)\b}",$type)) return "textarea";
    if(preg_match("{\b(BLOB|MEDIUMBLOB|LONGBLOB)\b}",$type)) return "textarea";
    return "input";
  }

  function getListCondition($field, $values, $istext=false)
  {
    if(is_bool($values)) return "$field=".(int)$values;
    if(!count($values)) return "0";
    if(!is_array($values)) $values=array($values);
    $single=count($values)==1;
    $values=implode(",",array_map($istext?"slashes":"intval",$values));
    return $single?"$field=$values":"$field IN ($values)";
  }

  function getSearchCondition($field, $value, $wholeword=false)
  {
    if(is_bool($value)) return "$field=".(int)$value;
    if(is_null($value) || $value==="") return "0";
    if($wholeword) $regexp="[[:<:]]".preg_quote($value)."[[:>:]]";
    $value=str_replace("\\","\\\\",$value);
    $value=str_replace("_","\_",$value);
    $value=str_replace("%","\%",$value);
    $extra=$wholeword?" AND $field REGEXP ".slashes($regexp):"";
    return "$field LIKE ".slashes("%$value%").$extra;
  }

  function getSearchSetCondition($field, $value, $separator=",")
  {
    if(is_bool($value)) $value=(int)$value;
    if($value==="") return "0";
    $field="CONCAT(".slashes($separator).",$field,".slashes($separator).")";
    $value=$separator.$value.$separator;
    $value=str_replace("\\","\\\\",$value);
    $value=str_replace("_","\_",$value);
    $value=str_replace("%","\%",$value);
    return "$field LIKE ".slashes("%$value%");
  }

  function getRangeCondition($field, $value)
  {
    if(is_bool($value)) return "$field=".(int)$value;
    $value=preg_replace("{[ \t\r\n]+}","",trim($value));
    $value=str_replace(",",".",$value);
    if($value==="") return "0";
    $simple=(float)$value;
    $pattern="{^([\+\-]?[\d\.]+)?(-)?([\+\-]?[\d\.]+)?\$}";
    $complex=preg_match($pattern,$value,$matches);
    if(!$complex || !ifset($matches[2])) return "$field=$simple";
    $min=ifset($matches[1],"")!=""?(float)$matches[1]:false;
    $max=ifset($matches[3],"")!=""?(float)$matches[3]:false;
    if($min===false && $max===false) return "$field=$simple";
    if($min===false) return "$field<=$max";
    if($max===false) return "$field>=$min";
    if($min>$max) swap($min,$max);
    return $min!=$max?"$field>=$min AND $field<=$max":"$field=$min";
  }

  function getListOrder($field, $values, $istext=false)
  {
    if(!is_array($values) || count($values)<2) return "NULL";
    $values=implode(",",array_map($istext?"slashes":"intval",$values));
    return "FIELD($field,$values)";
  }

  function getTablesList($full=false, $clearcache=false)
  {
    static $cache;
    if(!isset($cache) || $clearcache)
      { $cache=$this->customQuery("SHOW TABLES",false,0); sort($cache); }
    $restricted=$full?array():explodeSmart(",",DatabaseRestrictedTables);
    return array_values(array_diff($cache,$restricted));
  }

  function isTablePresent($table)
  {
    $tables=$this->getTablesList(true);
    return in_array($table,$tables);
  }

  function getDatabaseInformation($params=array())
  {
    if(!function_exists("mysqli_connect")) return false;
    extract($params);
    $result=array("installed"=>true);
    if(!isset($host) || !isset($db)) return $result;
    if(!isset($user) || !isset($pass)) return $result;
    $port=null; if(strpos($host,":")) list($host,$port)=explode(":",$host);
    if(!$connection=@mysqli_connect($host,$user,$pass,$db,$port)) return $result;
    if(!@mysqli_select_db($connection,$db)) return $result;
    $result["connected"]=true;
    if(!isset($charset)) return $result;
    if(!mysqli_query($connection,"ALTER DATABASE CHARACTER SET $charset")) return $result;
    return $result+compact("charset");
  }

  function getTablesInformation($full=false)
  {
    $result=array();
    $restricted=$full?array():explodeSmart(",",DatabaseRestrictedTables);
    $tables=$this->customQuery("SHOW TABLE STATUS");
    foreach($tables as $table) if(!in_array($table["Name"],$restricted)) {
      if(isset($table["Engine"])) $table["Type"]=$table["Engine"];
      $result[$table["Name"]]=array(
        "type"=>$this->internalTranslateTableType($table["Type"]),
        "rows"=>(int)$table["Rows"],
        "size"=>(int)$table["Data_length"]+(int)$table["Index_length"],
        "datasize"=>(int)$table["Data_length"],
        "indexsize"=>(int)$table["Index_length"],
        "counter"=>(int)$table["Auto_increment"],
        "comment"=>$table["Comment"]);
    }
    ksort($result);
    return $result;
  }

  function getTableInformation($table)
  {
    $fields=$this->customQuery("SHOW COLUMNS FROM $table");
    $info=$this->customQuery("SHOW INDEX FROM $table");
    $result=array("columns"=>array(),"uniques"=>array(),"indexes"=>array(),"fulltext"=>array());
    foreach($fields as $field) {
      if(phpcstrtoupper($field["Type"])=="TIMESTAMP"
        && $field["Default"]=="CURRENT_TIMESTAMP") $field["Default"]="";
      if(phpcstrtoupper($field["Type"])=="INT(11)") $field["Type"]="INT";
      $pattern="{^(\w+)\(?([^\)]*)\)?\s*(BINARY)?\s*(UNSIGNED)?\s*(ZEROFILL)?\$}i";
      preg_match($pattern,$field["Type"],$matches);
      while(count($matches)<6) $matches[]="";
      $attrs=0;
      if(phpcstrtoupper($field["Null"])=="YES") $attrs+=ColumnAttributeNull;
      if($matches[3]!="") $attrs+=ColumnAttributeBinary;
      if($matches[4]!="") $attrs+=ColumnAttributeUnsigned;
      if($matches[5]!="") $attrs+=ColumnAttributeZerofill;
      if(phpcstrtoupper($field["Extra"])=="AUTO_INCREMENT")
        $attrs+=ColumnAttributeCounter;
      $column=array();
      $column["name"]=$field["Field"];
      $column["type"]=phpcstrtoupper($matches[1]);
      $column["size"]=$matches[2];
      $column["attrs"]=$attrs;
      $column["default"]=$field["Default"];
      $result["columns"][]=$column;
    }
    foreach($info as $item) {
      $name=$item["Key_name"];
      $index=$item["Seq_in_index"];
      if(isset($item["Index_type"])) $item["Comment"]=$item["Index_type"];
      if($fulltext=phpcstrtoupper($item["Comment"])=="FULLTEXT") $item["Sub_part"]=0;
      if($item["Non_unique"]) $place=&$result["indexes"]; else $place=&$result["uniques"];
      $entry=array("name"=>$item["Column_name"],"size"=>(int)$item["Sub_part"]);
      if(!isset($place[$name])) $place[$name]=array();
      $place[$name][$index]=$entry;
      if($fulltext) $result["fulltext"][$name][$index]=$entry;
    }
    foreach($result as $subject=>$keys) if($subject!="columns") {
      foreach($keys as $name=>$key)
        { ksort($key); $result[$subject][$name]=array_values($key); }
      ksort($result[$subject]);
    }
    return $result;
  }

  function getFieldsList($table)
  {
    return $this->customQuery("SHOW COLUMNS FROM $table",false,"Field");
  }

  function isFieldPresent($table, $field)
  {
    $fields=$this->getFieldsList($table);
    return in_array($field,$fields);
  }

  function isLocalizedFieldPresent($table, $field)
  {
    $fields=$this->getFieldsList($table);
    $locales=explode(",",PhpcLocalesList);
    foreach($locales as $locale)
      if(in_array($field.$locale,$fields)) return true;
    return false;
  }

  function getKeyFields($table)
  {
    $info=$this->customQuery("SHOW INDEX FROM $table");
    $result=array();
    foreach($info as $line) if($line["Key_name"]=="PRIMARY")
      $result[$line["Seq_in_index"]]=$line["Column_name"];
    ksort($result);
    return array_values($result);
  }

  function getCounterValue()
  {
    return (int)mysqli_insert_id($this->connection);
  }

  function getLinesCount($table, $conditions="")
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="SELECT COUNT(*) FROM $table$conditions";
    return (int)$this->customQuery($query,true,0);
  }

  function getLinesFunction($table, $function, $conditions="")
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="SELECT $function FROM $table$conditions";
    return $this->customQuery($query,true,0);
  }

  function getMinField($table, $field, $conditions="")
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="SELECT MIN($field) FROM $table$conditions";
    $result=$this->customQuery($query,true,0);
    return is_null($result)?0:$result;
  }

  function getMaxField($table, $field, $conditions="")
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="SELECT MAX($field) FROM $table$conditions";
    $result=$this->customQuery($query,true,0);
    return is_null($result)?0:$result;
  }

  function getField($table, $field, $conditions="")
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="SELECT $field FROM $table$conditions LIMIT 1";
    $line=$this->customQuery($query,true);
    return ifset($line[$field]);
  }

  function getLine($table, $conditions="")
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="SELECT * FROM $table$conditions LIMIT 1";
    return $this->customQuery($query,true);
  }

  function isLinePresent($table, $conditions="")
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="SELECT 1 FROM $table$conditions LIMIT 1";
    return (bool)$this->customQuery($query,true,0);
  }

  function getLines($table, $conditions="")
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="SELECT * FROM $table$conditions";
    return $this->customQuery($query);
  }

  function getLinesRange($table, $offset, $count, $conditions="")
  {
    $offset=(int)$offset; $count=(int)$count;
    if($offset<0) { $count+=$offset; $offset=0; }
    if($conditions!=="") $conditions=" WHERE $conditions";
    $offset=$offset?"$offset,":"";
    $query="SELECT * FROM $table$conditions LIMIT $offset$count";
    return $count>0?$this->customQuery($query):array();
  }

  function getOrderedLines($table, $order, $conditions="")
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="SELECT * FROM $table$conditions ORDER BY $order";
    return $this->customQuery($query);
  }

  function getOrderedLinesRange($table, $order, $offset, $count, $conditions="")
  {
    $offset=(int)$offset; $count=(int)$count;
    if($offset<0) { $count+=$offset; $offset=0; }
    if($conditions!=="") $conditions=" WHERE $conditions";
    $offset=$offset?"$offset,":"";
    $query="SELECT * FROM $table$conditions ORDER BY $order LIMIT $offset$count";
    return $count>0?$this->customQuery($query):array();
  }

  function getDistinctField($table, $field, $conditions="")
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="SELECT DISTINCT $field FROM $table$conditions ORDER BY NULL";
    return $this->customQuery($query,false,$field);
  }

  function getOrderedDistinctField($table, $field, $conditions="")
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="SELECT DISTINCT $field FROM $table$conditions ORDER BY $field";
    return $this->customQuery($query,false,$field);
  }

  function getCounters($table, $field, $conditions="")
  {
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="SELECT $field,COUNT(*) AS counter FROM $table$conditions GROUP BY $field ORDER BY NULL";
    $assoc=array($field,"counter");
    return array_map("intval",$this->customQuery($query,false,$assoc));
  }

  function getRandomLine($table, $conditions="")
  {
    $count=$this->getLinesCount($table,$conditions);
    $result=$count?$this->getLinesRange($table,random($count),1,$conditions):array();
    return count($result)?$result[0]:false;
  }

  function getRandomLines($table, $limit=false, $conditions="")
  {
    $limit=$limit!==false?(" LIMIT ".(int)$limit):"";
    if($conditions!=="") $conditions=" WHERE $conditions";
    $query="SELECT * FROM $table$conditions ORDER BY RAND()$limit";
    return $this->customQuery($query);
  }

  function parseSQL($query)
  {
    $result=array();
    $length=strlen($query);
    $position=0;
    while($position<$length) {
      $char=$query[$position];
      if($char=="#") {
        $eolnn=strpos($query,"\n",$position+1);
        $eolnr=strpos($query,"\r",$position+1);
        if($eolnn===false) $eolnn=$length;
        if($eolnr===false) $eolnr=$length;
        $position=min($eolnn,$eolnr);
        continue;
      }
      if($char=="\t" || $char=="\r" || $char=="\n" || $char==" ") {
        $position++;
        continue;
      }
      $start=$position;
      do {
        $colon=$position<$length?strpos($query,";",$position):false;
        $quote=$position<$length?strpos($query,"'",$position):false;
        if($colon===false) $colon=$length;
        if($quote===false) $quote=$length;
        if($colon<=$quote) {
          $result[]=array("offset"=>$start,"length"=>$colon-$start);
          $position=$colon+1;
          break;
        }
        $position=$quote+1;
        do {
          $quote=$position<$length?strpos($query,"'",$position):false;
          $slash=$position<$length?strpos($query,"\\",$position):false;
          if($quote===false) $quote=$length;
          if($slash===false) $slash=$length;
          if($quote<=$slash) { $position=$quote+1; break; }
          $position=$slash+2;
        } while(true);
      } while(true);
    }
    return $result;
  }

  function exportTableStructure($table, $recreate=true)
  {
    static $tables=array();
    if(!isset($tables[$table]))
      $tables=extractArrayLines($this->customQuery("SHOW TABLE STATUS"),"Name");
    $info=ifset($tables[$table],array());
    $table=$this->internalNormalizeName($table);
    $fields=$this->customQuery("SHOW COLUMNS FROM $table");
    $indexes=$this->customQuery("SHOW INDEX FROM $table");
    $result=$keys=array();
    foreach($fields as $field) {
      $field["Field"]=$this->internalNormalizeName($field["Field"]);
      if(phpcstrtoupper($field["Type"])=="TIMESTAMP"
        && $field["Default"]=="CURRENT_TIMESTAMP") $field["Default"]="";
      preg_match("{^(\w+)(.*)\$}",$field["Type"],$matches);
      $field["Type"]=phpcstrtoupper($matches[1]).$matches[2];
      $field["Extra"]=phpcstrtoupper($field["Extra"]);
      if($field["Type"]=="INT(11)") $field["Type"]="INT";
      $field["Null"]=phpcstrtoupper($field["Null"])=="YES"?"NULL":"NOT NULL";
      $item="$field[Field] $field[Type] $field[Null]";
      if($field["Extra"]=="AUTO_INCREMENT") $item.=" AUTO_INCREMENT";
      if($field["Default"]) $item.=" DEFAULT ".slashes($field["Default"]);
      $item=preg_replace("{(?<= DATE NOT NULL) DEFAULT '0000-00-00'}","",$item);
      $result[]=$item;
    }
    $fulltext=false;
    foreach($indexes as $index) {
      $keyname=$this->internalNormalizeName($index["Key_name"]);
      $colname=$this->internalNormalizeName($index["Column_name"]);
      $name=($index["Non_unique"]?"":"UNIQUE ")."KEY $keyname";
      if($index["Key_name"]=="PRIMARY") $name="PRIMARY KEY";
      if(isset($index["Index_type"])) $index["Comment"]=$index["Index_type"];
      if(phpcstrtoupper($index["Comment"])=="FULLTEXT") {
        $name="FULLTEXT KEY $keyname";
        $index["Sub_part"]="";
        $fulltext=true;
      }
      if($index["Sub_part"]!="") $index["Sub_part"]="($index[Sub_part])";
      if(!isset($keys[$name])) $keys[$name]=array();
      $keys[$name][$index["Seq_in_index"]]="$colname$index[Sub_part]";
    }
    foreach($keys as $key=>$parts)
      { ksort($parts); $result[]="$key (".implode(",",$parts).")"; }
    if(isset($info["Engine"])) $info["Type"]=$info["Engine"];
    $type=$this->internalTranslateTableType($info["Type"]);
    $type=($type!="MyISAM" || $fulltext)?" ENGINE=$type":"";
    $comment=$info["Comment"]!=""?" COMMENT=".slashes($info["Comment"]):"";
    $result=implode(",\r\n  ",$result);
    $result="CREATE TABLE $table (\r\n  $result)$type$comment;\r\n";
    if($recreate) $result="DROP TABLE IF EXISTS $table;\r\n$result";
    return $result;
  }

  function exportTableLine($table, $values, $assoc=false, $replace=false)
  {
    foreach($values as $field=>$value)
      $values[$field]=$this->internalNormalizeValue($value,false);
    $method=$replace?"REPLACE":"INSERT";
    $fields=$assoc?" (".implode(",",array_keys($values)).")":"";
    $values=implode(",",$values);
    $result="$method INTO $table$fields VALUES ($values);\r\n";
    return $result;
  }

  function changeTableType($table, $type)
  {
    $query="ALTER TABLE $table ENGINE=$type";
    return $this->customQueryBoolean($query);
  }

  function changeTableComment($table, $comment)
  {
    $query="ALTER TABLE $table COMMENT=".slashes($comment);
    return $this->customQueryBoolean($query);
  }

  function renameTable($table, $name)
  {
    $query="ALTER TABLE $table RENAME TO $name";
    return $this->customQueryBoolean($query);
  }

  function optimizeTable($table)
  {
    $query="OPTIMIZE TABLE $table";
    $this->customQueryBoolean($query);
    $query="ALTER TABLE $table AUTO_INCREMENT=1";
    $this->customQueryBoolean($query);
  }

  function arrangeTable($table)
  {
    $keys=$this->getKeyFields($table);
    if(count($keys)) $keys=implode(",",$keys); else return;
    $query="ALTER TABLE $table ORDER BY $keys";
    $this->customQueryBoolean($query);
  }

  function repairTable($table)
  {
    $query="REPAIR TABLE $table";
    $this->customQueryBoolean($query);
  }

  function startTransaction()
  {
    $query="BEGIN";
    $this->customQuery($query);
  }

  function commitTransaction()
  {
    $query="COMMIT";
    $this->customQuery($query);
  }

  function rollbackTransaction()
  {
    $query="ROLLBACK";
    $this->customQuery($query);
  }

  function lockTable($table)
  {
    $query="LOCK TABLES $table WRITE";
    $this->customQuery($query);
  }

  function unlockTable($table)
  {
    $query="UNLOCK TABLES";
    $this->customQuery($query);
  }

  function clearCache()
  {
    $this->getTablesList(true,true);
  }
}

?>
