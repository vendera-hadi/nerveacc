<?php
namespace App\Services\DbDumper;

use Spatie\DbDumper\Databases\PostgreSql as PostgreSqlVendor;
use Symfony\Component\Process\Process;

class PostgreSql extends PostgreSqlVendor
{
    protected $socket = 'postgresql://';
    protected $storePath;
    protected $restorePath;

    // override set pg_dump path
    public function setDumpBinaryPath(string $dumpBinaryPath)
    {
        $this->dumpBinaryPath = $dumpBinaryPath;
        return $this;
    }

    public function setStorePath($path)
    {
        $this->storePath = $path;
        return $this;
    }

    public function setRestoreFile($path)
    {
        $this->restorePath = $path;
        return $this;
    }

	// override command maker buat windows
	public function getDumpCommand(string $dumpFile): string
    {
        $command = [
            "{$this->dumpBinaryPath}",
            "-F t",
            "--dbname=".$this->socket.$this->userName.":".$this->password."@".$this->host.":".$this->port."/".$this->dbName,
            "> ".$this->storePath,
        ];

        if ($this->useInserts) {
            $command[] = '--inserts';
        }

        foreach ($this->extraOptions as $extraOption) {
            $command[] = $extraOption;
        }

        if (! empty($this->includeTables)) {
            $command[] = '-t '.implode(' -t ', $this->includeTables);
        }

        if (! empty($this->excludeTables)) {
            $command[] = '-T '.implode(' -T ', $this->excludeTables);
        }

        return implode(' ', $command);
    }

    // override run command
    public function dumpToFile(string $dumpFile = '')
    {
        $this->guardAgainstIncompleteCredentials();

        $command = $this->getDumpCommand($dumpFile);
        // dd($command);
        $process = new Process($command);

        if (! is_null($this->timeout)) {
            $process->setTimeout($this->timeout);
        }
        $process->run();

        if($process->isSuccessful()) return true;
        return false;
    }

    public function restoreDB()
    {
        $command = [
            "{$this->dumpBinaryPath}",
            "--clean",
            "--dbname=".$this->socket.$this->userName.":".$this->password."@".$this->host.":".$this->port."/".$this->dbName,
            "< ".$this->restorePath,
        ];
        $command = implode(' ', $command);
        // dd($command);
        $process = new Process($command);
        if (! is_null($this->timeout)) {
            $process->setTimeout($this->timeout);
        }
        $process->run();

        if($process->isSuccessful()) return true;
        return false;
    }

    public function downloadBackup()
    {
        return response()->download($this->storePath);
    }
}