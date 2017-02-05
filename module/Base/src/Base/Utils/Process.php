<?php

namespace Base\Utils;

use Symfony\Component\Process\Process as SymfonyProcess;

class Process
{

    protected $scriptName;

    public function __construct($request)
    {
        if (method_exists($request, 'getScriptName')) {
            $this->scriptName = $request->getScriptName();
        } else {
            $this->scriptName = $request->getServer()->get('SCRIPT_FILENAME');
        }
    }

    public function start($command)
    {
        $process = new SymfonyProcess('php ' . $this->scriptName . ' ' . $command .'  > /dev/null &');
        $process->start();
    }

}
