<?php
/**
 * Part of the Platform application.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.  It is also available at
 * the following URL: http://www.opensource.org/licenses/BSD-3-Clause
 *
 * @package    Platform
 * @version    1.1.1
 * @author     Cartalyst LLC
 * @license    BSD License (3-clause)
 * @copyright  (c) 2011 - 2012, Cartalyst LLC
 * @link       http://cartalyst.com
 */

class Platform_Developers_API_Developers_Controller extends API_Controller
{

	protected $variable_start = "[[";
	protected $variable_end   = "]]";

	public function post_create_extension()
	{
		// Filesystem
		$filesystem = Filesystem::make('native');
		$file       = $filesystem->file();
		$directory  = $filesystem->directory();

		// Properties
		$name         = Input::get('name');
		$author       = Input::get('author');
		$description  = Input::get('description', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
		$version      = Input::get('version', '1.0');
		$vendor       = Input::get('vendor');
		$extension    = Input::get('extension');
		$handles      = Input::get('handles', $extension);
		$dependencies = Input::get('dependencies', array());
		$overrides    = Input::get('overrides', array());

		// Get root directory for created extension cache
		$root_directory = $this->create_root_directory();

		// Create the extension directory based on the vendor and extension name
		$extension_directory = $this->create_extension_directory($root_directory, $vendor, $extension);

		// Grab the stub directory for an extension.
		$extension_stub_directory = Bundle::path('platform/developers').'stubs'.DS.'creator'.DS.'extension'.DS.'extension';

		// We'll copy it over to the real directory
		$this->copy_contents($extension_stub_directory, $extension_directory);

		// Now, let's move the admin controller to the right filename
		if ($file->exists($admin_controller = $extension_directory.DS.'controllers'.DS.'admin'.DS.'admin.php'))
		{
			$file->move($admin_controller, dirname($admin_controller).DS.$extension.'.php');
		}

		// And the same with the public controller
		if ($file->exists($public_controller = $extension_directory.DS.'controllers'.DS.'public.php'))
		{
			$file->move($public_controller, dirname($public_controller).DS.$extension.'.php');
		}

		// Now, the theme working directory
		$theme_backend_directory  = $this->create_extension_theme_backend_directory($root_directory, $vendor, $extension);
		$theme_frontend_directory = $this->create_extension_theme_frontend_directory($root_directory, $vendor, $extension);

		// Get theme stub directories
		$theme_backend_stub_directory  = Bundle::path('platform/developers').'stubs'.DS.'creator'.DS.'extension'.DS.'theme'.DS.'backend';
		$theme_frontend_stub_directory = Bundle::path('platform/developers').'stubs'.DS.'creator'.DS.'extension'.DS.'theme'.DS.'frontend';

		// We'll copy it over to the real directory
		$this->copy_contents($theme_backend_stub_directory, $theme_backend_directory);
		$this->copy_contents($theme_frontend_stub_directory, $theme_frontend_directory);

		// Right, now we have the root directory, we need to recursively write out the variables
		$this->write_variables_recursively($root_directory, array(
			'name'                 => $name,
			'author'               => $author,
			'description'          => $description,
			'version'              => $version,
			'vendor'               => $vendor,
			'extension'            => $extension,
			'extension_classified' => Str::classify($extension),
			'namespace'            => str_replace('_', '\\', Str::classify($vendor.'_'.$extension)),
			'namespace_underscore' => Str::classify($vendor.'_'.$extension),
			'slug_code'            => $vendor.'.'.$extension,
			'slug_designer'        => $vendor.ExtensionsManager::VENDOR_SEPARATOR.$extension,
			'handles'              => $handles,
			'dependencies'         => ($dependencies[0] != '') ?

				// Have dependencies?
				'array('.implode(', ', array_map(function($override)
				{
					return '\''.$override.'\'';
				}, $dependencies)).')' :

				// Don't have dependencies
				'array()',

			'overrides'            => ($overrides[0] != '') ?

				// Have overrides?
				'array('.implode(', ', array_map(function($override)
				{
					return '\''.$override.'\'';
				}, $overrides)).')' :

				// Don't have overrides
				'array()',
		));

		// Generate the zip name.
		$zip_name = $vendor . '-' . $extension . '.zip';
		$temp_zip_name = time() . '-' . $zip_name;

		// Create the zip
		$zip_location = $this->create_zip($root_directory, $temp_zip_name);

		switch (Input::get('encoding', 'base64'))
		{
			case 'utf-8':
				$encoded = utf8_encode($file->contents($zip_location));
				break;

			default:
				$encoded = base64_encode($file->contents($zip_location));
				break;
		}

		// Now, remove the directory
		$directory->delete($root_directory);

		// Temporary fix for PHP on windows messing up the contents of the
		// ZIP when JSON encoded / decoded.
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public');
		header('Content-Description: File Transfer');
		header('Content-type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $zip_name . '"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . filesize($zip_location));
		ob_end_flush();
		@readfile($zip_location);

		// Remove the file.
		$file->delete($zip_location);

		return new Response($encoded);
	}

	public function post_create_theme()
	{
		// Filesystem
		$filesystem = Filesystem::make('native');
		$file       = $filesystem->file();
		$directory  = $filesystem->directory();

		// Properties
		$name        = Input::get('name');
		$author      = Input::get('author');
		$description = Input::get('description', 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.');
		$version     = Input::get('version', '1.0');
		$area        = Input::get('area', 'frontend');

		// Get root directory for created theme cache
		$root_directory = $this->create_root_directory();

		// Get theme stub directories
		$theme_stub_directory = Bundle::path('platform/developers').'stubs'.DS.'creator'.DS.'theme';

		$theme_directory = $this->{'create_theme_'.$area.'_directory'}($root_directory);

		// We'll copy it over to the real directory
		$this->copy_contents($theme_stub_directory, $theme_directory);

		// Right, now we have the root directory, we need to recursively write out the variables
		$this->write_variables_recursively($root_directory, array(
			'name'        => $name,
			'author'      => $author,
			'description' => $description,
			'version'     => $version,
		));

		// Generate the zip name.
		$zip_name = $name . '.zip';
		$temp_zip_name = time() . '-' . $zip_name;

		// Create the zip
		$zip_location = $this->create_zip($root_directory, $temp_zip_name);

		switch (Input::get('encoding', 'base64'))
		{
			case 'utf-8':
				$encoded = utf8_encode($file->contents($zip_location));
				break;

			default:
				$encoded = base64_encode($file->contents($zip_location));
				break;
		}

		// Now, remove the directory
		$directory->delete($root_directory);

		// Temporary fix for PHP on windows messing up the contents of the
		// ZIP when JSON encoded / decoded.
		header('Pragma: public');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Cache-Control: public');
		header('Content-Description: File Transfer');
		header('Content-type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . $zip_name . '"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Length: ' . filesize($zip_location));
		ob_end_flush();
		@readfile($zip_location);

		// Remove the file.
		$file->delete($zip_location);

		return new Response($encoded);
	}

	protected function create_zip($root_directory, $zip_name)
	{
		$zip_name = dirname($root_directory).DS.$zip_name;
		$zip = new ZipArchive();
		$zip->open($zip_name, ZipArchive::CREATE);

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$root_directory,
				RecursiveDirectoryIterator::SKIP_DOTS
			),
			RecursiveIteratorIterator::SELF_FIRST
		);

		$replacements = array(
			$root_directory.DS => '',
			DS                 => '/', // Zips don't like \ on Windows.
		);

		foreach ($iterator as $item)
		{
			$path = $item->getRealPath();

			if ($item->isDir())
			{
				$zip->addEmptyDir(str_replace(array_keys($replacements), array_values($replacements), $path));
			}
			else
			{
				$zip->addFile($path, str_replace(array_keys($replacements), array_values($replacements), $path));
			}
		}

		$zip->close();

		return $zip_name;
	}

	protected function write_variables_recursively($root_directory, array $variables)
	{
		// Prepare data
		$items              = new FilesystemIterator($root_directory, FilesystemIterator::SKIP_DOTS);
		$file               = Filesystem::make('native')->file();
		$prepared_variables = array();
		$variable_start     = $this->variable_start;
		$variable_end       = $this->variable_end;

		foreach ($variables as $name => $value)
		{
			$prepared_variables[$variable_start.$name.$variable_end] = $value;
		}

		foreach ($items as $item)
		{
			$real_path = $item->getRealPath();

			if ($item->isDir())
			{
				$this->write_variables_recursively($real_path, $variables);
			}
			else
			{
				// Do our replacements
				$result = str_replace(array_keys($prepared_variables), array_values($prepared_variables), $file->contents($real_path));
				$file->write($real_path, $result);
			}
		}
	}

	protected function create_root_directory()
	{
		// Prepare directories
		$work_directory  = path('storage').'work';
		$cache_directory = $work_directory.DS.'developers'.DS.'create'.DS.'cache';
		$root_directory  = $cache_directory.DS.time();

		Filesystem::make('native')->directory()->make($root_directory);

		return $root_directory;
	}

	protected function create_extension_directory($root_directory, $vendor, $extension)
	{
		$extension_directory = $root_directory.DS.'platform'.DS.'extensions'.DS.$vendor.DS.$extension;

		Filesystem::make('native')->directory()->make($extension_directory);

		return $extension_directory;
	}

	protected function create_extension_theme_backend_directory($root_directory, $vendor, $extension)
	{
		$theme_directory = $root_directory.DS.'public'.DS.'platform'.DS.'themes'.DS.'backend'.DS.'default'.DS.'extensions'.DS.$vendor.DS.$extension;

		Filesystem::make('native')->directory()->make($theme_directory);

		return $theme_directory;
	}

	protected function create_extension_theme_frontend_directory($root_directory, $vendor, $extension)
	{
		$theme_directory = $root_directory.DS.'public'.DS.'platform'.DS.'themes'.DS.'frontend'.DS.'default'.DS.'extensions'.DS.$vendor.DS.$extension;

		Filesystem::make('native')->directory()->make($theme_directory);

		return $theme_directory;
	}

	protected function create_theme_backend_directory($root_directory)
	{
		$theme_directory = $root_directory.DS.'public'.DS.'platform'.DS.'themes'.DS.'frontend'.DS.'default';

		Filesystem::make('native')->directory()->make($theme_directory);

		return $theme_directory;
	}

	protected function create_theme_frontend_directory($root_directory)
	{
		$theme_directory = $root_directory.DS.'public'.DS.'platform'.DS.'themes'.DS.'frontend'.DS.'default';

		Filesystem::make('native')->directory()->make($theme_directory);

		return $theme_directory;
	}

	protected function copy_contents($source, $destination)
	{
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator(
				$source,
				RecursiveDirectoryIterator::SKIP_DOTS
			),
			RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ($iterator as $item)
		{
			if ($item->isDir())
			{
				mkdir($destination.DS.$iterator->getSubPathName());
			}
			else
			{
				copy($item, $destination.DS.$iterator->getSubPathName());
			}
		}
	}

}