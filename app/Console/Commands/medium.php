<?php

namespace App\Console\Commands;

use App\Configs;
use App\ModelFunctions\PhotoFunctions;
use App\Photo;
use Illuminate\Console\Command;

class medium extends Command
{

	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'medium {nb=5 : generate medium pictures if missing} {tm=600 : timeout time requirement}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create medium pictures if missing';

	/**
	 * @var PhotoFunctions
	 */
	private $photoFunctions;

	/**
	 * Create a new command instance.
	 *
	 * @param PhotoFunctions $photoFunctions
	 * @return void
	 */
	public function __construct(PhotoFunctions $photoFunctions)
	{
		parent::__construct();

		$this->photoFunctions = $photoFunctions;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$argument = $this->argument('nb');
		$timeout = $this->argument('tm');
		set_time_limit($timeout);

		$photos = Photo::where('medium', '=', 0)->limit($argument)->get();
		if (count($photos) == 0) {
			$this->line('No pictures requires medium.');
			return false;
		}

		foreach ($photos as $photo) {
			if ($this->photoFunctions->createMedium(
				$photo,
				intval(Configs::get_value('medium_max_width')),
				intval(Configs::get_value('medium_max_height')))
			) {
				$photo->medium = 1;
				$photo->save();
				$this->line('medium for '.$photo->title.' created.');
			} else {
				$this->line('Could not create medium for '.$photo->title.'.');
			}
		}
	}
}
