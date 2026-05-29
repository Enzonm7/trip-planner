<?php
//Implement Builder.php and Config.php to create a strategy for calling the python script and getting the results
declare(strict_types=1);

final class PythonStrategy{
    private string $cmd = 'py C:/Users/user/Documents/Box_Certif/epreuve-finale/travel_page/test_algo/main.py';

    private array $descriptors = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];

    private $process;
    private $pipes;

    public function __construct() {
        $this->process = proc_open(
            $this->cmd,
            $this->descriptors,
            $this->pipes
        );
    }

    public function run($data): mixed {

        if (!is_resource($this->process)) {
            return null;
        }

        fwrite($this->pipes[0], $data);
        fclose($this->pipes[0]);

        $output = stream_get_contents($this->pipes[1]);
        fclose($this->pipes[1]);

        fclose($this->pipes[2]);

        proc_close($this->process);

        return json_decode($output, true);
    }
}
?>