<?php
namespace Core;

class ErrorSQLLogger implements \Doctrine\DBAL\Logging\SQLLogger
{

    public function startQuery($sql, array $params = null, array $types = null)
    {
        error_log($sql . PHP_EOL);
        
        if ($params) {
            error_log(var_dump($params));
        }
        
        if ($types) {
            error_log(var_dump($types));
        }
    }

    public function stopQuery()
    {}
}