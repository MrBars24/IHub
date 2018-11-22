<?php

namespace App\Console\Commands;

// App
use App\FileStorage;

// Laravel
use Illuminate\Console\Command;

class CleanupFiles extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'files:cleanup'; // @note: you can use "du -sh *" to recursively get sizes of directories

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Cleanup unused files';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		// get file system files
		$path = public_path('uploads/bodecontagion');
		$this->info($path);

		$directory = new \RecursiveDirectoryIterator($path);
		$iterator = new \RecursiveIteratorIterator($directory);

		// get files from database
		$files = FileStorage::query()
			->where('path', 'like', "$path%")
			->get()
			->keyBy('path');

		$limit = 400;
		$counter = 0;
		$totalSizeDeleted = 0;
		$filesDeleted = 0;
		foreach($iterator as $i => $info) {

			if($counter == 0) {
				vd($info);
			}

			$filepath = $info->getPathname();
			$found = isset($files[$filepath]) ? 'Found' : 'Not Found';
			$is_file = is_file($filepath) ? 'Is File' : 'Not a File';
			$this->info($filepath . ' -- ' . $found . ' -- ' . $is_file);

			if($counter == $limit) {
				break;
			}

			// delete orphaned file
			if(!isset($files[$filepath]) && is_file($filepath)) {
				$size = $info->getSize();
				unlink($filepath);
				$totalSizeDeleted += $size;
				$filesDeleted++;
				$this->info('deleted ' . $filepath . ' (' . $filesDeleted . ' files using ' . $totalSizeDeleted . ' bytes of disk space deleted so far)');
			}

			$counter++;
		}

		$this->info('total files: ' . $counter);
		$this->info('total deleted: ' . $filesDeleted . ' files using ' . $totalSizeDeleted . ' bytes of disk space');
	}
}
