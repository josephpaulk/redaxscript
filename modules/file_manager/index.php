<?php

/**
 * file manager loader start
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Modules
 * @author Henry Ruhs
 */

function file_manager_loader_start()
{
	if (LOGGED_IN == TOKEN && FIRST_PARAMETER == 'admin' && SECOND_PARAMETER == 'file-manager')
	{
		global $loader_modules_styles, $loader_modules_scripts;
		$loader_modules_styles[] = 'modules/file_manager/styles/file_manager.css';
		$loader_modules_scripts[] = 'modules/file_manager/scripts/startup.js';
		$loader_modules_scripts[] = 'modules/file_manager/scripts/file_manager.js';
	}
}

/**
 * file manager loader scripts transport start
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Modules
 * @author Henry Ruhs
 */

function file_manager_loader_scripts_transport_start()
{
	if (LOGGED_IN == TOKEN && FIRST_PARAMETER == 'admin' && SECOND_PARAMETER == 'file-manager')
	{
		$output = languages_transport(array(
			'file_manager_browse',
			'file_manager_upload'
		));
		echo $output;
	}
}

/**
 * file manager render start
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Modules
 * @author Henry Ruhs
 */

function file_manager_render_start()
{
	if (LOGGED_IN == TOKEN && FIRST_PARAMETER == 'admin' && SECOND_PARAMETER == 'file-manager')
	{
		define('CENTER_BREAK', 1);
		define('TITLE', l('file_manager_file_manager'));
	}
}

/**
 * file manager center start
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Modules
 * @author Henry Ruhs
 */

function file_manager_center_start()
{
	if (LOGGED_IN == TOKEN && FIRST_PARAMETER == 'admin' && SECOND_PARAMETER == 'file-manager')
	{
		if (THIRD_PARAMETER == 'upload')
		{
			file_manager_upload(FILE_MANAGER_DIRECTORY);
		}
		else if (THIRD_PARAMETER == 'delete')
		{
			if ( TOKEN_PARAMETER == '')
			{
				$error = l('token_incorrect');
			}
			else
			{
				/* file manager directory object */

				$file_manager_directory = New Redaxscript_Directory(FILE_MANAGER_DIRECTORY);
				$file_manager_directory_string = $file_manager_directory->get(ID_PARAMETER);

				/* remove related children */

				$file_manager_directory->remove($file_manager_directory_string);
			}
		}

		/* handle error */

		if ($error)
		{
			notification(l('error_occurred'), $error, l('back'), 'admin/file-manager');
		}

		/* handle success */

		else
		{
			file_manager(FILE_MANAGER_DIRECTORY);
		}
	}
}

/**
 * file manager admin panel panel list modules
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Modules
 * @author Henry Ruhs
 *
 * @return string
 */

function file_manager_admin_panel_list_modules()
{
	$output = '<li>' . anchor_element('internal', '', '', l('file_manager_file_manager'), 'admin/file-manager') . '</li>';
	return $output;
}

/**
 * file manager
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Modules
 * @author Henry Ruhs
 *
 * @param string $directory
 */

function file_manager($directory = '')
{
	if (!is_dir($directory))
	{
		mkdir($directory, 0777);
	}
	if (!is_dir($directory))
	{
		$output = '<div class="box_note note_error">' . l('file_manager_directory_create') . l('colon') . ' ' . $directory . l('point') . '</div>';
	}
	else if (!is_writable($directory))
	{
		$output = '<div class="box_note note_error">' . l('file_manager_directory_permission_grant') . l('colon') . ' ' . $directory . l('point') . '</div>';
	}

	/* collect listing output */

	$output .= '<h2 class="title_content">' . l('file_manager_file_manager') . '</h2>';
	$output .= form_element('form', 'form_file_manager', 'js_form_file_manager form_file_manager', '', '', '', 'action="' . REWRITE_ROUTE . 'admin/file-manager/upload" method="post" enctype="multipart/form-data"');
	$output .= form_element('file', '', 'js_file field_file hide_if_js', 'file', '', l('file_manager_browse'));
	$output .= '<button type="submit" class="js_upload field_upload button_admin hide_if_js">' . l('file_manager_upload') . '</span></span></button>';
	$output .= '</form>';
	$output .= '<div class="wrapper_table_admin"><table class="table table_admin">';

	/* collect thead and tfoot */

	$output .= '<thead><tr><th class="s4o6 column_first">' . l('name') . '</th><th class="s1o6 column_second">' . l('file_manager_file_size') . '</th><th class="s1o6 column_last">' . l('date') . '</th></tr></thead>';
	$output .= '<tfoot><tr><td class="column_first">' . l('name') . '</td><th class="column_second">' . l('file_manager_file_size') . '</th><td class="column_last">' . l('date') . '</td></tr></tfoot>';

	/* file manager directory object */

	$file_manager_directory = New Redaxscript_Directory($directory);
	$file_manager_directory_array = $file_manager_directory->get();

	/* collect directory output */

	if (count($file_manager_directory_array))
	{
		$output .= '<tbody>';
		foreach ($file_manager_directory_array as $key => $value)
		{
			$output .= '<tr><td class="column_first">';
			$route = $directory . '/' . $value;
			if (function_exists('exif_imagetype') && exif_imagetype($route))
			{
				$output .= anchor_element('external', '', '', $value, ROOT . '/' . $route);
			}
			else
			{
				$output .= $value;
			}

			/* collect control output */

			$output .= '<ul class="list_control_admin"><li class="item_delete">' . anchor_element('internal', '', 'js_confirm', l('delete'), 'admin/file-manager/delete/' . $key . '/' . TOKEN) . '</li></ul>';

			/* collect filesize and filetime output */

			$output .= '</td><td class="column_second">' . ceil(filesize($route) / 1024) . ' Kb</td><td class="column_last">' . date(s('date'), filectime($route)) . '</td></tr>';
		}
		$output .= '</tbody>';
	}
	else
	{
		$error = l('file_manager_file_no') . l('point');
	}

	/* handle error */

	if ($error)
	{
		$output .= '<tbody><tr><td colspan="2">' . $error . '</td></tr></tbody>';
	}
	$output .= '</table></div>';
	echo $output;
}

/**
 * file manager clean file name
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Modules
 * @author Henry Ruhs
 *
 * @param string $input
 * @return string
 */

function file_manager_clean_file_name($input = '')
{
	$output = trim(strtolower($input));
	$output = preg_replace('/[-|\s+]/i', '_', $output);
	$output = preg_replace('/[^a-z0-9._]/i', '', $output);
	return $output;
}

/**
 * file manager upload
 *
 * @since 1.2.1
 * @deprecated 2.0.0
 *
 * @package Redaxscript
 * @category Modules
 * @author Henry Ruhs
 *
 * @param string $diretory
 */

function file_manager_upload($directory = '')
{
	$file = $_FILES['file']['tmp_name'];
	$file_name = file_manager_clean_file_name($_FILES['file']['name']);
	$file_size = $_FILES['file']['size'];

	/* validate post */

	if (function_exists('exif_imagetype'))
	{
		if (exif_imagetype($file) == '')
		{
			$error = l('file_manager_file_type_limit') . l('point');
		}
	}

	/* if filesize limit */

	if ($file_size > 1048576)
	{
		$error = l('file_manager_file_size_limit') . l('point');
	}

	/* handle error */

	if ($error)
	{
		$output = '<div class="box_note note_warning">' . $error . '</div>';
		echo $output;
	}
	else
	{
		move_uploaded_file($file, $directory . '/' . $file_name);
	}
}
?>