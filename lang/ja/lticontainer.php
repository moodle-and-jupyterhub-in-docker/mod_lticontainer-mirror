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
 * @package     mod_lticontainer
 * @category    string
 * @copyright   2021 Fumi.Iseki <iseki@rsch.tuis.ac.jp>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Date Time Format
$string['datetime_format'] = 'Y/m/d H:i';
$string['datetime_format_s'] = 'Y/m/d H:i:s';
$string['datetime_format_m'] = 'm/d H:i';

// module
$string['pluginname'] = 'LTIContainer';
$string['modulename'] = 'LTIContainer';
$string['modulenameplural'] = 'LTIContainer';

// mod_form
$string['lticontainername']  = 'Name ';
$string['description'] = 'Description';
$string['lticontainername_help'] = 'Name of this instance of module.';
$string['lticontainer_container_set'] = 'Settings of Container and LTI';
$string['lticontainer_chart_set'] = 'Settings of Dashboard and Charts';
$string['docker_host'] = 'Container host';
$string['docker_host_help'] = 'IP address or FQDN of Docker/Podman host.';
$string['docker_user'] = 'Container user';
$string['docker_user_help'] = 'Name of user that belongs Docker/Podman group on container host. This user should not have an executable shell.';
$string['docker_pass'] = 'Password of container user';
$string['docker_pass_help'] = 'Password of Docker/Podman user.';
$string['show_custom_params'] = 'Show custom parameters';
$string['show_custom_params_help'] = 'Show custom parameters at LTI Edit.';
$string['imagename_filter'] = 'Image name filter words';
$string['imagename_filter_help'] = 'Set filtering keywords when image names are displayed. If a keyword is prefixed with a minus sign, files with that keyword are excluded.';
$string['make_docker_volumes'] = 'Does mod_lticontainer create container volumes ?';
$string['make_docker_volumes_help'] = 'If No, Docker/Podman volumes are careated by JupyterHub. If Yes, Docker/Podman volumes are careated by mod_lticontainer when LTI Edit Tab is saved.';
$string['use_podman'] = 'Container system';
$string['use_podman_help'] = 'Select the container system (Docker or Podman) to be used.';
$string['jupyterhub_url'] = 'JupyterHub (container host) URL';
$string['jupyterhub_url_help'] = 'Specify the JupyterHub (Docker/Podman host) URL in the format "Protocol_Name://Host_Name[:Port_Number]". However, do not use IP addess for "Host_Name". Please use FQDN as "Host_Name".';
$string['api_token'] = 'JupyterHub API Token';
$string['api_token_help'] = 'Specify the same value as <strong>api_token</strong> at <strong>JupyterHub.services</strong> in the lticontainer JupyterHub configuration file. At least 8 characters are required. This is used when using the JupyterHub API for admin-service.';
$string['rpc_token'] = 'XML-RPC Token for the Web services';
$string['rpc_token_help'] = 'Specify the <strong>Web services</strong> Token of <strong>Jupyter Notebook Data</strong>. This is used when using the Dashboard/Charts function.';

$string['use_dashboard'] = 'Use Dashboard function';
$string['use_dashboard_help'] = 'If Yes, Dashboard tab is shown. But you need to execute the Feserver.';

$string['during_realtime'] = 'Time period of Real Time Charts (s)';
$string['during_realtime_help'] = 'The time period(second) displayed by real time charts.';
$string['during_anytime'] = 'Time period of Any Time Charts (s)';
$string['during_anytime_help'] = 'The time period(second) displayed by any time charts.';
$string['chart_bar_usernum'] = 'Max users in Bar chart';
$string['chart_bar_usernum_help'] = 'Maximum number of users in a Bar chart.';
$string['chart_bar_codenum'] = 'Max code cells in Bar chart';
$string['chart_bar_codenum_help'] = 'Maximum number of code cells in a Bar chart.';
$string['chart_line_usernum'] = 'Max users in Line chart';
$string['chart_line_usernum_help'] = 'Maximum number of users in a Line chart.';
$string['chart_line_interval'] = 'Max interval time of line data (s)';
$string['chart_line_interval_help'] = 'Maximum interval time(second) between each line data.';

// print_error
$string['access_forbidden'] = 'Access forbidden.';
$string['invalid_sesskey'] = 'Invalid session key.';
$string['no_data_found'] = 'No data found!';
$string['no_ltiid_found'] = 'lti_id is not displyed or not exist!';
$string['web_homedir_forbidden'] = 'The web server process does not have write access to its own home directory. Please check the permissions.';
$string['no_docker_command'] = 'docker command does not exist (/usr/bin/docker). Please install docker package.';
$string['no_podman_command'] = 'podman-remote or podman command does not exist (/usr/bin/podman-remote or /usr/bin/podman). Please install podman-remote package.';
$string['missmatch_jh_api_token'] = 'Missmatch api token for JupyterHub. Please check "JupyterHub API Token" at "Edit settings" page.';

// Capability / パンくずメニュー
$string['lticontainer:admin_tools'] = 'Use admin tools';
$string['lticontainer:chart_view'] = 'View charts';
$string['lticontainer:dashboard_view'] = 'View dashboard';
$string['lticontainer:db_write'] = 'Write db';
$string['lticontainer:jupyterhub_api'] = 'Operate JupyterHub API';
$string['lticontainer:jupyterhub_api_edit'] = 'JupyterHub API (POST/PUT/DELETE)';
$string['lticontainer:jupyterhub_user'] = 'Operate JupyterHub users';
$string['lticontainer:jupyterhub_user_edit'] = 'Edit JupyterHub users';
$string['lticontainer:lti_edit'] = 'Edit LTI custom paramaters';
$string['lticontainer:lti_setting'] = 'Select LTI instances to display';
$string['lticontainer:lti_view'] = 'View LTI instances';
$string['lticontainer:volume_edit'] = 'Edit volumes';
$string['lticontainer:volume_view'] = 'Operate volumes';
$string['lticontainer:show_demo'] = 'Show demo';
$string['lticontainer:over_view'] = 'Over view';

// Tab
$string['over_view_tab'] = 'Over View';
$string['show_demo_tab'] = 'Demo';
$string['volume_view_tab'] = 'Docker Volumes';
$string['volume_delete_tab'] = 'Delete Volumes';
$string['jhuser_delete_tab'] = 'Delete JupyterHub User';
$string['lti_view_tab'] = 'LTI Connections';
$string['lti_edit_tab'] = 'LTI Edit';
$string['lti_setting_tab'] = 'LTI Settings';
$string['dashboard_view_tab'] = 'Dashboard';
$string['chart_view_tab'] = 'Charts';
$string['jupyterhub_api_tab'] = 'JupyterHub';
$string['jupyterhub_user_tab'] = 'JupyterHub User';
$string['admin_tools_tab'] = 'Admin Tools';
$string['returnto_course_tab'] = 'Return to course';

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
$string['deletevolconfirm']  = 'Do you really delete these volumes ?';
$string['deleteuserconfirm'] = 'Do you really delete these users ?';

// Table: JupyterHub API
$string['user_name'] = 'UserName';
$string['user_role'] = 'Role';
$string['user_status'] = 'Status';
$string['user_server'] = 'Server';
$string['user_last'] = 'Last Activity';
$string['user_del'] = 'Delete';

// User Name
$string['username_manage'] = 'Pattern of names displayed';
$string['username_manage_help'] = 'You can select displied name pattern in this module.';

// Passed Time
$string['never_ago']   = 'Never';
$string['seconds_ago'] = 'seconds ago';
$string['minutes_ago'] = 'minutes ago';
$string['hours_ago']   = 'hours ago';
$string['days_ago']    = 'days ago';
$string['months_ago']  = 'months ago';
$string['years_ago']   = 'years ago';

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
$string['wiki_url'] = 'https://gitlab.nsl.tuis.ac.jp/iseki/mod_lticontainer/-/wikis/mod_lticontainer';
$string['git_url']  = 'https://gitlab.nsl.tuis.ac.jp/iseki/mod_lticontainer';
$string['Edit'] = 'Edit';
$string['Edit_settings'] = 'Edit settings';
$string['Edit_display_settings'] = 'Edit display settings';

