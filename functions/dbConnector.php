<?php

    /**
     * Etablie une connexion avec la BDD
     *
     * @return PDO
     */
    function connectToDb(): PDO
    {
        try
        {
            $dbDsn = 'mysql:dbname=tp_auth_php;host=127.0.0.1;port=3306';
            $dbUser = 'root';
            $dbPassword = '';
            
            $db = new PDO($dbDsn, $dbUser, $dbPassword);
	        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            return $db;
        }
        catch (\PDOException $exception)
        {
            die("Error connexion to database: " . $exception->getMessage());
        }
    }



?>