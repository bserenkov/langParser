<?php

$phar = new Phar('langParser.phar', 0, 'langParser');
// just required when modifiyng stub
$phar->startBuffering();
$phar->buildFromDirectory(__DIR__, '/\.php$/');
$stub = "#!/usr/bin/env php \n" . $phar->createDefaultStub('run.php');
$phar->setStub($stub);

$phar->stopBuffering();
?>