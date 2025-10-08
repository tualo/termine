<?php

namespace Tualo\Office\Termine\Commands;

use Garden\Cli\Cli;
use Garden\Cli\Args;
use Tualo\Office\Basic\ISetupCommandline;
use Tualo\Office\Basic\PostCheck;
use Tualo\Office\Basic\BaseSetupCommand as BaseSetup;

class Setup extends BaseSetup implements ISetupCommandline
{

    public static function getCommandName(): string
    {
        return 'termine';
    }
    public static function getCommandDescription(): string
    {
        return 'perform a complete termine setup';
    }
    public static function setup(Cli $cli)
    {
        $cli->command(self::getCommandName())
            ->description(self::getCommandDescription())
            ->opt('client', 'only use this client', true, 'string');
    }
    public static function run(Args $args)
    {
        $clientName = $args->getOpt('client');
        if (is_null($clientName)) $clientName = '';

        PostCheck::formatPrintLn(['blue'], "Installing all needed SQL procedures for termine module");
        PostCheck::formatPrintLn(['blue'], "==========================================================");


        $installCommands = [
            'install-sessionsql-bsc-main',
            'install-sql-sessionviews',
            'install-sql-bsc-main-ds',
            'install-sql-bsc-menu',
            'install-sql-ds-main',
            'install-sql-ds',
            'install-sql-ds-dsx',
            'install-sql-ds-privacy',
            'install-sql-ds-docsystem',
            'install-sql-termine'
            // 'compile'
        ];

        foreach ($installCommands as $cmdString) {
            self::performInstall($cmdString, $clientName);
        }
    }
}
