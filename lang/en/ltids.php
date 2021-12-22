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
 * @package     mod_ltids
 * @category    string
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// module
$string['pluginname'] = 'LTIContainerSpawner';
$string['modulename'] = 'LTIContainerSpawner';
$string['modulenameplural'] = 'LTIConrainerSpawners';

// mod_form
$string['ltidsname']  = 'Name ';
$string['description'] = 'Description';
$string['ltidsname_help'] = 'Name of this instance of module';
$string['ltidsfieldset'] = 'Options of LTIContainerSpawner module';
$string['docker_host'] = 'Docker/Podman host';
$string['docker_host_help'] = 'IP address or FQDN of Docker/Podman host';
$string['docker_user'] = 'Docker/Podman user';
$string['docker_user_help'] = 'Name of user that belongs Docker/Podman group on docker/Podman host. This user should not have an executable shell';
$string['docker_pass'] = 'Password of Docker/Podman user';
$string['docker_pass_help'] = 'Password of Docker/Podman user';
$string['show_custom_params'] = 'Show custom parameters';
$string['show_custom_params_help'] = 'Show custom parameters at LTI edit';
$string['imagename_filter'] = 'Image name filter words';
$string['imagename_filter_help'] = 'Set filtering keywords when image names are displayed';
$string['make_docker_volumes'] = 'Does mod_ltids create docker volumes ?';
$string['make_docker_volumes_help'] = 'If No, docker volumes are careated by JupyterHub. If Yes, docker volumes are careated by mod_ltids when LTI Edit Tab is saved';
$string['use_podman'] = 'Use Podman instead of Docker';
$string['use_podman_help'] = 'It supports Podman instead of Docker. LTIPodmanSpawner is needed for Jpyterhub. This function is experimentary, so any features will not work.';

// print_error
$string['access_forbidden'] = 'Access forbidden';
$string['invalid_sesskey'] = 'Invalid session key';
$string['no_data_found'] = 'No data found!';
$string['web_homedir_forbidden'] = 'The web server process does not have write access to its own home directory. Please check the permissions';
$string['no_docker_command'] = 'docker command does not exist (/usr/bin/docker). Please install docker package';
$string['no_podman_command'] = 'podman-remote or podman command does not exist (/usr/bin/podman-remote or /usr/bin/podman). Please install podman-remote package';

// パンくずメニュー
$string['ltids:over_view'] = 'Over View';
$string['ltids:show_demo'] = 'Demo';
$string['ltids:volume_view'] = 'Volumes';
$string['ltids:lti_view'] = 'LTI Connections';
$string['ltids:lti_edit'] = 'LTI Edit';

// Tab
$string['over_view_tab'] = 'Over View';
$string['show_demo_tab'] = 'Demo';
$string['volume_view_tab'] = 'Docker Volumes';
$string['volume_delete_tab'] = 'Delete Volumes';
$string['lti_view_tab'] = 'LTI Connections';
$string['lti_edit_tab'] = 'LTI Edit';
$string['lti_setting_tab'] = 'LTI Settings';
$string['returnto_course_tab'] = 'Return to course';

$string['dashboard_view_tab'] = 'Dashboard';
$string['dump_db_tab'] = 'Dump DB';
$string['api_experiment_tab'] = 'API Experiment';


// Table: LTI connect/edit
$string['lti_name'] = 'LTI Name';
$string['custom_command'] = 'Command';
$string['users/image'] = 'Users/Image';
$string['command_options'] = 'Options';
$string['volume_role'] = 'Volume Role';
$string['volume_name'] = 'Display Name';
$string['access_name'] = 'Access Name';
$string['access_users'] = 'Accessible Users';

// Table: LTI volume
$string['volume_view'] = 'Volumes';
$string['driver_name'] = 'Driver';
$string['volume_del'] = 'Delete';
$string['deletevolconfirm'] = 'Do you really delete these volumes ?';

// Title
$string['users_cmd_ttl'] = 'Accessible users';
$string['teachers_cmd_ttl'] = 'Teachers';
$string['image_cmd_ttl'] = 'Container image';
$string['vol_cmd_ttl'] = 'Task volume';
$string['sub_cmd_ttl'] = 'Submit volume';
$string['prs_cmd_ttl'] = 'Personal volume';
$string['lab_url_cmd_ttl'] = 'Default URL';
$string['cpugrnt_cmd_ttl'] = 'CPU Guarantee';
$string['memgrnt_cmd_ttl'] = 'Memory Guarantee';
$string['cpulimit_cmd_ttl'] = 'CPU Limit';
$string['memlimit_cmd_ttl'] = 'Memory Limit';

// view.php
$string['wiki_url'] = 'https://gitlab.nsl.tuis.ac.jp/iseki/mod_ltids/-/wikis/mod_ltids';
$string['git_url']  = 'https://gitlab.nsl.tuis.ac.jp/iseki/mod_ltids';
$string['Edit'] = 'Edit';
$string['Edit_settings'] = 'Edit settings';


