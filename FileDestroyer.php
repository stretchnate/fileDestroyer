<?
	/**
	 * This class deletes files and folders in a given path
	 */
	class FileDestroyer {
		const   NEW_LINE          = "\n";

		private $dir              = "/";
		private $filename;
		private $eliminate_type; //dir|file|both
		private $recurse          = false;
		private $dry_run          = true;
		private $use_wildcard     = false;

		private static $wildcard              = '*';
		private static $directories_to_ignore = array(".", "..");

		public function __construct() {}

		/**
		 * main method for deleting file
		 *
		 * @return void
		 * @access public
		 * @throws Exception
		 */
		public function execute() {
			$iterator = new DirectoryIterator($this->dir);

			if(!$this->filename) {
				throw new Exception("No filename provided");
			} elseif(!$this->eliminate_type) {
				throw new Exception("No eliminate type provided (dir|file|both)");
			} else {
				$this->destroy($iterator);
			}
		}

		/**
		 * iterates through a directory deleting files that match $this->filename
		 *
		 * @param object $iterator (DirectoryIterator)
		 * @return void
		 * @access private
		 */
		private function destroy(DirectoryIterator $iterator) {
			//iterate through the directory
			while($iterator->valid()) {
				$destroy_file = false;
				if($iterator->getFilename() == $this->filename || $this->filename == '*' || ($this->use_wildcard == true && strpos($iterator->getFilename(), $this->filename) !== false)) {
					switch($this->eliminate_type) {
						case 'file':
							if($iterator->isFile()) {
								$destroy_file = !$this->dry_run;
								if($this->dry_run === true) {
									echo "(Dry run) removing " . $iterator->getPathname() . self::NEW_LINE;
								}
							} elseif($iterator->isDir() && $this->recurse === true && !in_array($iterator->getFilename(), self::$directories_to_ignore)) {
								$new_iterator = new DirectoryIterator($iterator->getPathname());
								$this->destroy($new_iterator);
								if($this->dry_run === true) {
									echo "(Dry run) removing " . $iterator->getPathname() . self::NEW_LINE;
								}
							}
							break;

						case 'dir':
							if($iterator->isDir() && !in_array($iterator->getFilename(), self::$directories_to_ignore)) {
								$fd = new FileDestroyer();
								$fd->setRecurse(true);
								$fd->setEliminateType('both');
								$fd->setFilename('*');
								$fd->setDirectory($iterator->getPathname());
								$fd->setDryRun($this->dry_run);
								$fd->execute();

								$destroy_file = !$this->dry_run;
								if($this->dry_run === true) {
									echo "(Dry run) removing " . $iterator->getPathname() . self::NEW_LINE;
								}
							}
							break;

						case 'both':
							if(!in_array($iterator->getFilename(), self::$directories_to_ignore)) {
								if($iterator->isDir()) {
									$fd = new FileDestroyer();
									$fd->setRecurse(true);
									$fd->setEliminateType('both');
									$fd->setFilename('*');
									$fd->setDirectory($iterator->getPathname());
									$fd->setDryRun($this->dry_run);
									$fd->execute();
								}

								$destroy_file = !$this->dry_run;
								if($this->dry_run === true) {
									echo "(Dry run) removing " . $iterator->getPathname() . self::NEW_LINE;
								}
							}
							break;
					}

					if($destroy_file === true) {
						echo "removing " . $iterator->getPathname() . self::NEW_LINE;
						if($iterator->isDir()) {
							rmdir($iterator->getPathname());
						} elseif($iterator->isFile()) {
							unlink($iterator->getPathname());
						}
					}
				} elseif ($iterator->isDir() && $this->recurse === true && !in_array($iterator->getFilename(), self::$directories_to_ignore)) {
					$new_iterator = new DirectoryIterator($iterator->getPathname());
					$this->destroy($new_iterator);
				}

				$iterator->next();
			}
		}

		/**
		 * converts a non-boolean value to boolean ('false' will be converted to false)
		 *
		 * @param  mixed  $value
		 * @return boolean
		 * @since 04.24.2013
		 */
		private function getBoolean($value) {
			$result = false;

			if($value !== 'false') {
				$result = (bool)$value;
			}

			return $result;
		}

		public function setDirectory($directory) {
			if(preg_match("/^[A-Z]+:(\\|\/)/", trim($directory)) !== false) {
				$this->dir = $directory;
			} else {
				$this->dir .= ltrim($directory, "/");
			}
		}

		public function setRecurse($recurse) {
			$this->recurse = $this->getBoolean($recurse);
			return $this;
		}

		public function setDryRun($dry_run) {
			$this->dry_run = $this->getBoolean($dry_run);
			return $this;
		}

		public function setUseWildcard($use_wildcard) {
			$this->use_wildcard = $this->getBoolean($use_wildcard);
			return $this;
		}

		public function setFilename($filename)            { $this->filename       = $filename;       return $this; }
		public function setEliminateType($eliminate_type) { $this->eliminate_type = $eliminate_type; return $this; }
	}