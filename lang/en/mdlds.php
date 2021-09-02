<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin strings are defined here.
 *
 * @package     mod_mdlds
 * @category    string
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// module
$string['pluginname'] = 'MDLDockerSpawner';
$string['modulename'] = 'Moodle DockerSpawner';
$string['modulenameplural'] = 'MDLDockerSpawners';

// mod_form
$string['mdldsname']  = 'Name ';
$string['description'] = 'Description';
$string['mdldsname_help'] = 'Name of this instance of module';
$string['mdldsfieldset'] = 'Options of Moodle DockerSpawner module';
$string['docker_host'] = 'Docker host';
$string['docker_host_help'] = 'IP address or FQDN of docker host';
$string['docker_user'] = 'Docker user';
$string['docker_user_help'] = 'Name of user that belongs docker group on docker host. This user should not have an executable shell';
$string['docker_pass'] = 'Password of docker user';
$string['docker_pass_help'] = 'Password of docker user';
$string['show_custom_params'] = 'Show custom parameters';
$string['show_custom_params_help'] = 'Show custom parameters at LTI edit';
$string['make_docker_volumes'] = 'Does mod_mdlds create docker volumes';
$string['make_docker_volumes_help'] = 'If No, docker volumes are careated by JupyterHub. If Yes, docker volumes are careated by mod_mdlds at LTI Edit Tab';

// print_error
$string['access_forbidden'] = 'Access forbidden';
$string['invalid_sesskey'] = 'Invalid session key';
$string['no_data_found'] = 'No data found!';
$string['web_homedir_forbidden'] = 'The web server process does not have write access to its own home directory. Please check the permissions of {$a}';
$string['no_docker_command'] = 'docker command does not exist ({$a})';

// パンくずメニュー
$string['mdlds:over_view'] = 'Over View';
$string['mdlds:show_demo'] = 'Demo';
$string['mdlds:volume_view'] = 'Volumes';
$string['mdlds:lti_connect'] = 'LTI Connections';
$string['mdlds:lti_edit'] = 'LTI Edit';

// tab
$string['over_view'] = 'Over View';
$string['show_demo'] = 'Demo';
$string['view_volumes'] = 'Docker Volumes';
$string['lti_connect'] = 'LTI Connections';
$string['lti_edit'] = 'LTI Edit';
$string['lti_setting'] = 'LTI Settings';
$string['returnto_course'] = 'Return to course';

// Table: LTI connect/edit
$string['lti_name'] = 'LTI Name';
$string['custom_command'] = 'Command';
$string['users/image'] = 'Users/Image';
$string['command_option'] = 'Option';
$string['volume_role'] = 'Volume Role';
$string['volume_name'] = 'Display Name';
$string['access_name'] = 'Access Name';
$string['access_users'] = 'Accessible Users';

// Table: LTI volume
$string['volume_view'] = 'Volumes';
$string['driver_name'] = 'Driver';
$string['volume_delete'] = 'Delete';

// Title
$string['user_cmd_ttl'] = 'Accessible users';
$string['teacher_cmd_ttl'] = 'Teachers';
$string['image_cmd_ttl'] = 'Container image';
$string['vol_cmd_ttl'] = 'Task volume';
$string['sub_cmd_ttl'] = 'Submit volume';
$string['prs_cmd_ttl'] = 'Personal volume';
$string['lab_url_cmd_ttl'] = 'Sub URL';

// view.php
$string['wiki_url'] = 'https://www.nsl.tuis.ac.jp/xoops/modules/xpwiki/?mod_mdlds';
$string['Edit'] = 'Edit';
$string['Edit_settings'] = 'Edit settings';


