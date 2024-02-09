<?php declare(strict_types=1);

namespace UMA\FpvJpApi\DI;

use Doctrine\DBAL\Logging\SQLLogger;

class EchoSQLLogger implements SQLLogger
{
    public function startQuery($sql, array $params = null, array $types = null)
    {
        error_log($sql);

        if ($params) {
            error_log("Parameters: " . print_r($params, true));
        }

        if ($types) {
            error_log("Types: " . print_r($types, true));
        }

    }

    public function stopQuery()
    {

    }
}