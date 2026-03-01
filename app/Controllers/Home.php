<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index()
    {
        return redirect()->to('/quintanares.php');
    }

    public function logTest()
    {
        $line = date('Y-m-d H:i:s') . " INFO log-test endpoint hit\n";
        $logPath = rtrim(WRITEPATH, "\\/") . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'log-' . date('Y-m-d') . '.log';

        log_message('info', 'log-test endpoint hit');
        @file_put_contents($logPath, $line, FILE_APPEND);

        return $this->response->setStatusCode(200)->setBody($logPath);
    }
}
