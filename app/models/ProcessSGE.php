<?php
/* An easy way to keep in track of external processes.
 * Ever wanted to execute a process in php, but you still wanted to have somewhat controll of the process ?
 * Well.. This is a way of doing it.
 * @compability: Linux only. (Windows does not work).
 * @author: Peec
 */

namespace App\Models;

class ProcessSGE extends Model{

   public function status($pid) {

			$pidForm = sprintf("%7s",$pid);

			// to check all the running jobs:
			//$command = $this->global['sge']['qstat'].'-u www-data | grep "^   *"'
			//$command = $this->global['sge']['qstat'].'-u www-data | grep "^'.$pidForm.'"';

			$command = sprintf($this->global['sge']['qstat'], $this->global['sge']['host'], $pidForm);

      exec($command,$op);

			$output = preg_replace('!\s+!', ' ', $op);

			$output = preg_split('/ /', $output[0]);

			$status = $output[5];

			if(!isset($status)) {
				return 5;
			} else { 
				switch($status) {
					case 'qw':
					case 'r': return 4;
										break;
					case 'Eqw':
					case 'e': return 3;
										break;
					default: return 4;
				}
			}
		
		}

		public function start($workdir, $path) {

			//$command = $this->global['sge']['qsub']."$path 2>&1";

			$command = sprintf($this->global['sge']['qsub'], $this->global['sge']['host'], $path);

			//chdir($workdir);
			
			exec($command, $op);

			# Your job 408247 ("blablabla.sh") has been submitted
			$output = preg_split('/ /', $op[0]);
			$pid = $output[2];

			//var_dump($command, $op, $output, $pid);

			// logger!!!!
			// logger("Job $output ($pid). $err");
			
			//return 0;

			return (int)$pid;

    }

		public function stop($pid) {

			//$command = $this->global['sge']['qdel'].' '.$pid;
			$command = sprintf($this->global['sge']['qdel'], $this->global['sge']['host'], $pid);

      exec($command, $op);

			return $this->status($pid);

		}

}


