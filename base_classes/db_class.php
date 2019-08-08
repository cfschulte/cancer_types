<?php
// db_class.php -  Thu Mar 1 09:49:47 CST 2018
//   This is a rewrite/cleanup of the db_class I have been using.

// Since the calling files already have essentials required, requiring
// it again conflicts. It seems odd since require_once is supposed to 
// only 
// require_once "../essentials.php";  

require_once "db_config.php";
  
/////////////////////
// Encapsulating a safe usage of MySQL using mysqli 
class db_class 
{
    protected  $conn;

 // initiator     
    function db_class(/* we might pass defaults here */) {                
        global $DATABASE;
        global $DB_USER;
        global $DB_PASSWORD;
        global $DB_SERVER;

        try {
//             $this->conn = @new mysqli($DB_SERVER, $DB_USER, $DB_PASSWORD , $DATABASE, $DB_PORT);
            $this->conn = @new mysqli($DB_SERVER, $DB_USER, $DB_PASSWORD , $DATABASE);
            if ($this->conn->connect_error) {
                throw new Exception('Database connection failed again: ' . $this->conn->connect_error);
            }
        } catch(Exception $e) {
            echo "problem connecting to the database: " . $e->getLine() . ' - ' . $e->getMessage();
        }
    }

/////////////////////////////////
// tester for checking if we need backwards compatiblity 
    function hasNativeDriver() {
        if(function_exists('mysqli_fetch_all')) {
            return "yes, this has the native driver" ;
        } else {
            return "NOPE, no native driver";
        }
    } 
   

  /////////////////////////////////
  // Basic description, because it's useful...    
    function tableDescription($table_title) {
         $sql = "DESC $table_title";
        return $this->getTableNoParams($sql);
    }

  /////////////////////////////////
  // It is used for getting the types of a table's columns to use in
  // a bound sql query. Sets text to s, whole numbers to i, decimals 
  // to d, and binary data to b. It returns a has of column names and 
  // type declaration 
    function columnTypeHash($table_title) {
        $desc_table = $this->tableDescription($table_title);
        $descHash = array() ; // for 5.3 compatablility 

        foreach($desc_table as $col_desc) {
            $type = $col_desc['Type'];
            $typeChar = 's';  // default 
            if(preg_match("/int/i", $type) || preg_match("/bool/i", $type)){
                $typeChar = 'i';
            } elseif (preg_match("/real/i", $type) || preg_match("/float/i", $type) 
                   || preg_match("/dec/i", $type) || preg_match("/numer/i", $type) 
                   || preg_match("/doub/i", $type)) {
                $typeChar = 'd';
            }elseif (preg_match("/bin/i", $type) || preg_match("/image/i", $type) ) {
                $typeChar = 'b';
            }
        
            $descHash[$col_desc['Field']] = $this->getTypeChar($type);
        }
        
        return $descHash;
    }

  /////////////////////////////////
  // Basically, this is the same as the previous function, but
  // It also adds the actual type, so one can ensure the correct 
  // date format.
    function columnTypeHashLong($table_title) {
        $desc_table = $this->tableDescription($table_title);
        $descHash = array() ; // for 5.3 compatablility 

        foreach($desc_table as $col_desc) {
            $type = $col_desc['Type'];
        
            $descHash[$col_desc['Field']] = array('type'=> $type, 
                                          'typeChar' => $this->getTypeChar($type));
        }
        
        return $descHash;
    }

  /////////////////////////////////
  // Get the character for proper SQL binding.
    function getTypeChar($type) {
        $typeChar = 's';  // default 
        if(preg_match("/int/i", $type) || preg_match("/bool/i", $type)){
            $typeChar = 'i';
        } elseif (preg_match("/real/i", $type) || preg_match("/float/i", $type) 
               || preg_match("/dec/i", $type) || preg_match("/numer/i", $type) 
               || preg_match("/doub/i", $type)) {
            $typeChar = 'd';
        }elseif (preg_match("/bin/i", $type) || preg_match("/image/i", $type) ) {
            $typeChar = 'b';
        }
        
        return $typeChar;
    }  
  
  /////////////////////////////////
  // PRIMARY KEY 
  function getPrimaryKey( $table ){
    global $DATABASE;
    $sql = "SELECT k.column_name
            FROM information_schema.table_constraints t
            JOIN information_schema.key_column_usage k
            USING(constraint_name,table_schema,table_name)
            WHERE t.constraint_type='PRIMARY KEY'
              AND t.table_schema='$DATABASE'
              AND t.table_name='$table'";
              
    $db_table = $this->getTableNoParams($sql);
    return $db_table[0]['column_name'];
  }
  
  /////////////////////////////////
  // GET A QUICK COUNT  
  function tableCount( $table ){
      $primary_key = $this->getPrimaryKey($table);
      
      $sql = "SELECT COUNT(" . $primary_key . ") FROM " . $table;
      
      $db_table = $this->getTableNoParams($sql);
      return $db_table[0]["COUNT(" . $primary_key . ")"];
  }
  
  /////////////////////////////////
  // NOTE: This assumes that the table has a field called 'id'
  // does not work 
    function rowExists($table, $id) {
        $sql = "SELECT EXISTS(SELECT 1 FROM $table WHERE id=$id)";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute();
            $mysqli_result = $stmt->get_result();
            return $mysqli_result;
    }
    
/////////////////////////////////
// I guess this is still an interesting wrapper. It assumes the table has 
// a primary key called, 'id'
	function getRowByID($id, $table) {
	    $sql = "SELECT * FROM $table WHERE id=?";
	    return $this->simpleOneParamRequest($sql, 'i', $id);
	}
  
  /////////////////////////////////
  // Returns an array of rows, represented as associative arrays with 
  // column => values
    function getTableNoParams($sql) {
        try {
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute();
            
            if(empty($result)){
                throw new Exception($this->conn->error_list);
            } 
            
            $mysqli_result = $stmt->get_result();
            return $mysqli_result->fetch_all(MYSQLI_ASSOC); 
            
        } catch (Exception $e) {
            echo "Fail in getTableNoParams $sql: " . $e->getLine() . 
                 ": " . $this->conn->error;;
            return null;
        }
        
        // Older versions of db_class check to see if $this->has_native_driver
        // is true. From here on out, we won't worry too much about it. 
    }
    

  /////////////////////////////////
  // used to get only a couple of columns from a row 
    function simpleOneParamRequest($sql, $type, $queryVal) {
        try {
            $stmt = $this->prepBindExOneParam($sql, $type, $queryVal);
            
            $mysqli_result = $stmt->get_result();
            return $mysqli_result->fetch_all(MYSQLI_ASSOC) ;
        } catch (Exception $e) {
            echo "Fail in simpleOneParamRequest $sql: " . $e->getLine()  . 
                 ": " . $this->conn->error;
            return null;        
        }
    }


/////////////////////////////////
// For quick updates when I have FULL CONTROL of the parameters
// and no parameters are being passed that need to be properly 
// bound.
    function simpleExecute($sql) {
        try {
            $stmt = $this->conn->prepare($sql);
            if (! $stmt) {
                throw new Exception();
            }    
            $result = $stmt->execute();
            if(empty($result)){
                throw new Exception('failed execute');
            } 
          } catch (Exception $e) {
            echo "Fail in prepBindExOneParam $sql: " . $e->getLine()  . 
                  ": " . $this->conn->error .  ": " . $e->getMessage();
            return null;        
        }
          
        return $this->conn->affected_rows;
    }
    



/////////////////////////////////
// e.g. delete a a row 
    function simpleOneParamUpdate($sql, $type, $queryVal) {
        $stmt = $this->prepBindExOneParam($sql, $type, $queryVal);

        return $this->conn->affected_rows;
    }


  /////////////////////////////////
  // Preparee, bind, execute, and return the mysqli_result
    function  prepBindExOneParam($sql, $type, $queryVal){
        try {
            $stmt = $this->conn->prepare($sql);
            if (! $stmt) {
                throw new Exception();
            }    
            $result = $stmt->bind_param($type, $queryVal);    
            if(empty($result)){
                throw new Exception('failed bind_param');
            } 
            $result = $stmt->execute();
            if(empty($result)){
                throw new Exception('failed execute');
            } 
            return $stmt;
            
        } catch (Exception $e) {
            echo "Fail in prepBindExOneParam $sql: " . $e->getLine()  . 
                  ": " . $this->conn->error .  ": " . $e->getMessage();
            return null;        
        }
    }

  //////////////////////////////
  // This is like safeInsertUpdateDelete except that it will return a table. 
  //  It is useful for more selective or inclusive search queries 
    function safeSelect($sql, $typeStr, $paramList) {
        $stmt = $this->prepBindExMultiParams($sql, $typeStr, $paramList);
        
        $mysqli_result = $stmt->get_result();
        return $mysqli_result->fetch_all(MYSQLI_ASSOC); 
    }

  /////////////////////////////////
  // create a safe sql statement and execute it 
  // This is the workhorse
  /* Bind parameters. Types: s = string, i = integer, d = double,  b = blob */
    function safeInsertUpdateDelete($sql, $typeStr, $paramList) {
        try {
            $stmt = $this->prepBindExMultiParams($sql, $typeStr, $paramList);
            $result = $stmt->affected_rows;
            if($result < 0 ) {
                throw new Exception();
            }
            return $result;
        } catch (Exception $e) {
            echo "Fail in safeInsertUpdateDelete $sql: " . $e->getLine() . 
                 ": " . $this->conn->error;
            return null;        
        }
       
    }


  /////////////////////////////////
  // Preparee, bind, execute, and return the mysqli_result
  // This is where most of the errors occur. 
    function  prepBindExMultiParams($sql, $typeStr, $paramList){
        try {
            $stmt = $this->conn->prepare($sql);
            if (! $stmt) {
                throw new Exception();
            }    
            
            // MULTI BIND
            // turn typeStr and paramList into one array? 
            // TODO: better understand php callbackParams and call_user_func_array 
            $callbackParams = array();
            $callbackParams[] = & $typeStr;
            // pass the getTableNoParamsresses of the escaped parameters to the statement 
            $n = count($paramList);
            for( $i=0; $i < $n; $i++ ) {
                $callbackParams[] = & $paramList[$i];
            }
            
            $result = call_user_func_array(array($stmt, "bind_param"), $callbackParams);
            if(!$result) {
                throw new Exception("Funk in the call_user_func_array ");
            }

            $result = $stmt->execute();
            if(empty($result)){
                throw new Exception();
            } 
            return $stmt;
        } catch(Exception $e) {
            echo "Fail in prepBindExMultiParams $sql: " . $e->getLine()  . 
                 ": " . $this->conn->error;
            return null;        
        }
    }

  //////////////////////////////////////////////////////////
  // A few higher level functions

  //////////////////////////////
  // I've been creating insert/update commands for a 
  // while and they tend to be very similar. 
    function buildAndExecuteInsert($table, $data_elements, $auto_col_to_skip = '') {
        $typeHashLong = $this->columnTypeHashLong($table);
        
       // Build the insert string
        $sql = 'INSERT INTO ' . $table ;
        $columnStr = ' (';
        $valueStr = '(';
        $typeList = '';
        $paramList = array();
    
//         $debug = array();
       // build the data elements of the sql string
        foreach ($data_elements as $col => $val) {
//             $debug[] = $col;
            // Skip this column if it is the auto_increment (e.g. id)
            if($col == $auto_col_to_skip) {
                continue;
            } 
            
            if( array_key_exists($col, $typeHashLong) ){
                $typeStruct = $typeHashLong[$col];
                
                if( sizeof($paramList) > 0 ) {
                    $columnStr .= ',';
                    $valueStr  .= ',';
                }
                $columnStr .= $col;
                $valueStr  .= '?';
                $typeList  .= $typeStruct['typeChar'];
                $paramList[] = ensureType($val, $typeStruct['type']);
            }
        }
    
        $columnStr .= ')';
        $valueStr  .= ')';
        $sql .= $columnStr . ' VALUES ' . $valueStr;
        
//         return array( $sql, $typeList, $paramList );
        
        $result = $this->safeInsertUpdateDelete($sql, $typeList, $paramList);
        
        return $result;
    }

  //////////////////////////////
  // Updates are a little trickier because they tend to be more specific
  // this assumes that there is an id and it is the primary key and is 
  // an integer. The $data_elements array must have an id
    function buildAndExecuteUpdate($table, $data_elements ){
        try {
           // make sure there is an id.
            if( !array_key_exists('id', $data_elements) ){
                throw new Exception("No id in data_elements");
            }
            $id = $data_elements['id'];
           // Don't update the row's id. Unset it so it isn't 
           // included in the foreach.
            unset( $data_elements['id'] );
            
            $typeHashLong = $this->columnTypeHashLong($table);
            $sql = 'UPDATE ' . $table . ' SET ';
            
            $typeList = '';
            $paramList = array();
            
           // Which columns get set?
            foreach($data_elements as $col => $val) {
                if( array_key_exists($col, $typeHashLong) ){
                    $typeStruct = $typeHashLong[$col];
                    
                    if( sizeof($paramList) > 0 ){
                        $sql .= ',';
                    }
                    $sql .= "$col=?";
                    $typeList  .= $typeStruct['typeChar'];
                    $paramList[] = ensureType($data_elements[$col], $typeStruct['type']);
                }
            }
            
            $sql .= ' WHERE id=?';
            $typeList  .= 'i';
            $paramList[] = $id;
            
//             return array('typeHashLong'=> $typeHashLong, 'data_elements' => $data_elements);
//            return array($sql, $typeList, $paramList); 
            $result = $this->safeInsertUpdateDelete($sql, $typeList, $paramList);
            
            return $result;
        } catch (Exception $e) {
            echo "Fail in buildAndExecuteUpdate " . $e->getLine()  . 
                 ": " . $this->conn->error;
            return null;        
        }
    }

  //////////////////////////////
  ///
    function hasForeignKey($table, $column) {
        $table_desc = $this->tableDescription($table);
        
        foreach($table_desc as $col_desc){
            // find the column 
            if($col_desc['Field'] == $column) {
                // check if it has a MUL key
                if($col_desc['Key'] == 'MUL'){
                    return 1;
                }
            }
        }
        
        return 0;
    }

  //////////////////////////////
  /// Got this from https://stackoverflow.com/questions/806989/how-to-find-all-tables-that-have-foreign-keys-that-reference-particular-table-co
  // user sonance207  -- though altered heavily.
    function getFKeyParentTable($table, $column, $referenced_column='id') {
      $sql = "SELECT  ke.REFERENCED_TABLE_NAME parentTable, ke.TABLE_NAME childTable, ke.COLUMN_NAME ChildColumnName";
      $sql .= " FROM information_schema.KEY_COLUMN_USAGE ke";
      $sql .= " WHERE ke.referenced_table_name IS NOT NULL";
      $sql .= " AND ke.REFERENCED_COLUMN_NAME = '$referenced_column'";
      $sql .= " AND ke.TABLE_NAME = '$table'";
      $sql .= " AND ke.COLUMN_NAME =  '$column' ";
      
//       $stmt = $this->conn->prepare($sql);
//       return $stmt;
      $table = $this->getTableNoParams($sql);
      
      
      
      return $table[0]['parentTable'];
    }

    
  //////////////////////////////
  ///
    function getLastDBError() {
      return $this->conn->error_list;
    }
    
   /////////////////////////////////
   // Get the id of the last insert call with auto increment. 
   // It is up to the programmer to use it when appropriate.
     function lastInsertedID() {
        return $this->conn->insert_id;
     }
    

  /////////////////////////////////
  // escapes values like Pete "Mac" McKenzi  - as it turns out, this is not needed 
    function escapeVal($value) {
        return $this->conn->real_escape_string($value);
    }
    
  /////////////////////////////////
  // Gets the last row of a table (the most recently created in dho_users);
    function getLastRow($table) {
        try {
            $primary_key = $this->getPrimaryKey($table);
            $sql = "SELECT * FROM $table ORDER BY $primary_key DESC LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            if (! $stmt) {
                throw new Exception();
            }    
            $result = $stmt->execute();
            if(empty($result)){
                throw new Exception();
            } 
            $mysqli_result = $stmt->get_result();
            return $mysqli_result->fetch_assoc();
        }catch(Exception $e) {
            echo "Fail in getLastRow: " . $e->getLine()  . 
                 ": " . $this->conn->error;
            return null;        
        }
    }

  /////////////////////////////////
  // final close - wrapper for closing the connection 
    function closeDB() {
        $this->conn->close();
    }
    
  /////////////////////////////////////////////////////////////////////////////
  /////////////////////////////////////////////////////////////////////////////
  // Let's see if we can group some static functions with this. Some of these 
  // will assume (for the moment anyway) that an object has been created 

  /////////////////////////////////
  // Generic Insert -- we have this up above 
//    public static function genInsertStatement($table, $col_val_hash, $colTypeHashLong){}

}

