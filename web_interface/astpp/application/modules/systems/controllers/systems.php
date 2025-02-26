<?php

// ##############################################################################
// ASTPP - Open Source VoIP Billing Solution
//
// Copyright (C) 2016 iNextrix Technologies Pvt. Ltd.
// Samir Doshi <samir.doshi@inextrix.com>
// ASTPP Version 3.0 and above
// License https://www.gnu.org/licenses/agpl-3.0.html
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU Affero General Public License for more details.
//
// You should have received a copy of the GNU Affero General Public License
// along with this program. If not, see <http://www.gnu.org/licenses/>.
// ##############################################################################
class Systems extends MX_Controller
{

    function __construct()
    {
        parent::__construct();
        $this->load->helper('template_inheritance');
        $this->load->helper('file');
        $this->load->library('session');
        $this->load->library("system_form");
        $this->load->library('astpp/form', "system_form");
        $this->load->library('form_validation');
        $this->load->library('ASTPP_Sms');
        $this->load->model('system_model');
        $this->load->model('Astpp_common');
        $this->load->dbutil();

        if ($this->session->userdata('user_login') == FALSE)
            redirect(base_url() . '/astpp/login');
    }

    function configuration_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Settings');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "system", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $data['form'] = $this->form->build_form($this->system_form->get_configuration_form_fields(), $edit_data);
        $this->load->view('view_configuration_add_edit', $data);
    }

    function configuration_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);
        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('configuration_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'systems/configuration/');
        }
    }

    function configuration_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('did_search', "");
    }

    function configuration_save()
    {
        $add_array = $this->input->post();
        $data['form'] = $this->form->build_form($this->system_form->get_configuration_form_fields(), $add_array);
        if ($add_array['id'] != '') {
            $data['page_title'] = gettext('Edit Settings');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $this->system_model->edit_configuration($add_array, $add_array['id']);
                echo json_encode(array(
                    "SUCCESS" => gettext("setting updated successfully!")
                ));
                exit();
            }
        }
    }

    function configuration($group_title = '')
    {
        if ($group_title == "") {
            redirect(base_url() . '/dashboard');
        }
        if ($group_title == "email") {
            $data['test_email_flag'] = true;
        }
        $data['username'] = $this->session->userdata('user_name');
        if ($group_title == 'payment_methods' || $group_title == 'ported_number') {
            $page_title = str_replace("_", " ", $group_title);
            $data['page_title'] = ucwords($page_title);
        } else {
            $data['page_title'] = gettext(ucfirst($group_title));
        }
        $data['group_title'] = $group_title;
        $data['details'] = $this->system_model->get_subcategory_menu($group_title);

        $add_array = $this->input->post();
        if (! empty($add_array)) {
            if ($add_array['tax_type'] != '') {
                $selected_tax = implode(",", $add_array['tax_type']);
                $add_array['tax_type'] = $selected_tax;
                if ($add_array['tax_type'] != '0') {
                    $add_array['tax_type'] = str_replace('0,', '', $add_array['tax_type']);
                }
            }
            if (isset($add_array['version'])) {
                unset($add_array['version']);
            }
            foreach ($add_array as $key => $val) {
                $update_array = array(
                    'value' => $val
                );
                $this->system_model->edit_configuration($update_array, $key);
            }
            $this->session->set_flashdata('astpp_errormsg', gettext(sprintf('%s Settings updated sucessfully!', ucfirst($group_title))));

            redirect(base_url() . 'systems/configuration/' . $group_title);
        } else {
            $data['menu'] = $this->system_model->get_system_sidemenue();
            $this->load->view('view_systemconf', $data);
        }
    }

    function configuration_json()
    {
        $json_data = array();
        $count_all = $this->system_model->getsystem_list(false, "", "");
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->system_model->getsystem_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->system_form->build_system_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
    }

    function template()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Templates');
        $data['search_flag'] = true;
        $this->session->set_userdata('advance_search', 0);
        $data['grid_fields'] = $this->system_form->build_template_list_for_admin();
        $data["grid_buttons"] = $this->system_form->build_grid_buttons();
        $data['form_search'] = $this->form->build_serach_form($this->system_form->get_template_search_form());
        $this->load->view('view_template_list', $data);
    }

    function template_json()
    {
        $json_data = array();
        $count_all = $this->system_model->gettemplate_list(false, "", "");
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->system_model->gettemplate_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->system_form->build_template_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
    }

    function template_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Email template');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "default_templates", $where);
        $template_words = array();
        foreach ($account->result_array() as $key => $value) {
            $subject = $this->common->getContents($value['subject'], '#', '#');
            $template = $this->common->getContents($value['template'], '#', '#');
            $sms_template = $this->common->getContents($value['sms_template'], '#', '#');
            $edit_data = $value;
        }
        $template_words = array_merge($subject, $template, $sms_template);
        $data['template_words'] = $template_words;
        $data['form'] = $this->form->build_form($this->system_form->get_template_form_fields(), $edit_data);
        $this->load->view('view_template_add_edit', $data);
    }

    function template_save()
    {
        $add_array = $this->input->post();
        $template = preg_replace('<!-- (.|\s)*? -->', '', $add_array['template']);
        $add_array['template'] = str_replace("&lt;", "", str_replace("&gt;", "", $template));
        if ($this->session->userdata('logintype') == 1 || $this->session->userdata('logintype') == 5) {
            $account_data = $this->session->userdata("accountinfo");
            $reseller = $account_data['id'];
            $this->resellertemplate_save($add_array, $reseller);
        } else {
            $this->admintemplate_save($add_array);
        }
    }

    function resellertemplate_save($data, $resellerid)
    {
        $where = array(
            'name' => $data['name'],
            'reseller_id' => $resellerid
        );
        $count = $this->db_model->countQuery("*", "default_templates", $where);
        $data['form'] = $this->form->build_form($this->system_form->get_template_form_fields(), $data);
        if ($count > 0) {
            $data['page_title'] = gettext('Edit Template');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
            } else {

                $this->system_model->edit_resellertemplate($data, $data['id']);
                $this->session->set_flashdata('astpp_errormsg', gettext('Template Updated Successfully!'));
                redirect(base_url() . 'systems/template/');
                exit();
            }
        } else {
            if ($this->form_validation->run() == FALSE) {
                $data['page_title'] = gettext('Edit Template');
                $data['validation_errors'] = validation_errors();
            } else {
                unset($data['form']);
                $data['reseller_id'] = $resellerid;
                $this->system_model->add_resellertemplate($data);
                $this->session->set_flashdata('astpp_errormsg', gettext('Template Added Successfully!'));
                redirect(base_url() . 'systems/template/');
                exit();
            }
        }
        $this->load->view('view_template_add_edit', $data);
    }

    function admintemplate_save($data)
    {
        $data['form'] = $this->form->build_form($this->system_form->get_template_form_fields(), $data);
        if ($data['id'] != '') {
            $data['page_title'] = gettext('Edit Template');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
            } else {
                unset($data['form']);
                unset($data['page_title']);
                $this->system_model->edit_template($data, $data['id']);
                $this->session->set_flashdata('astpp_errormsg', gettext('Template updated successfully!'));
                redirect(base_url() . 'systems/template/');
                exit();
            }
        } else {
            $data['page_title'] = gettext('Termination Details');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
            } else {
                unset($data['form']);
                $this->system_model->add_template($data);
                $this->session->set_flashdata('astpp_errormsg', gettext('Template added successfully!'));
                redirect(base_url() . 'systems/template/');
                exit();
            }
        }
        $this->load->view('view_template_add_edit', $data);
    }

    function template_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('template_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'systems/template/');
        }
    }

    function template_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('did_search', "");
    }

    function country_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Countries');
        $data['search_flag'] = true;
        $data['cur_menu_no'] = 4;
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('country_list_search', '');
        $data['grid_fields'] = $this->system_form->build_country_list_for_admin();
        if ($this->session->userdata('logintype') == 2) {
            $data["grid_buttons"] = $this->system_form->build_admin_grid_buttons();
        } else {
            $data["grid_buttons"] = json_encode(array());
        }
        $data['form_search'] = $this->form->build_serach_form($this->system_form->get_search_country_form());
        $this->load->view('view_country_list', $data);
    }

    function country_list_json()
    {
        $json_data = array();
        $count_all = $this->system_model->getcountry_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];
        $query = $this->system_model->getcountry_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->system_form->build_country_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
    }

    function country_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('country_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'systems/country_list/');
        }
    }

    function country_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('country_list_search', "");
    }

    function country_add()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = gettext('Add Country');
        $data['form'] = $this->form->build_form($this->system_form->get_country_form_fields(), '');
        $this->load->view('view_country_add_edit', $data);
    }

    function country_list_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Country');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "countrycode", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $data['form'] = $this->form->build_form($this->system_form->get_country_form_fields($edit_id), $edit_data);
        $this->load->view('view_country_add_edit', $data);
    }

    function country_save()
    {
        $add_array = $this->input->post();
        $data['form'] = $this->form->build_form($this->system_form->get_country_form_fields($add_array['id']), $add_array);
        if ($add_array['id'] != '') {
            $data['page_title'] = gettext('Edit Country');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $this->system_model->edit_country($add_array, $add_array['id']);
                echo json_encode(array(
                    "SUCCESS" => gettext(sprintf('%s country updated successfully!', $add_array["country"]))
                ));
                exit();
            }
        } else {
            $data['page_title'] = gettext('Add Country');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $response = $this->system_model->add_country($add_array);
                echo json_encode(array(
                    "SUCCESS" => gettext(sprintf('%s country added successfully!', $add_array["country"]))
                ));
                exit();
            }
        }
    }

    function country_remove($id)
    {
        $this->system_model->remove_country($id);
        $country = $this->common->get_field_name('country', 'countrycode', $id);
        $this->session->set_flashdata('astpp_notification', gettext(sprintf('%s Country removed successfully!', $country)));

        redirect(base_url() . 'systems/country_list/');
    }

    function country_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        echo $this->db->delete("countrycode");
    }

    function currency_list()
    {
        $base_currency = Common_model::$global_config['system_config']['base_currency'];
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Currencies');
        $data['search_flag'] = true;
        $data['cur_menu_no'] = 4;
        $this->session->set_userdata('currency_search', 0);
        $data['grid_fields'] = $this->system_form->build_currency_list_for_admin();
        if ($this->session->userdata('logintype') == 2) {
            $data["grid_buttons"] = $this->system_form->build_admin_currency_grid_buttons();
        } else {
            $data["grid_buttons"] = json_encode(array());
        }
        $data['form_search'] = $this->form->build_serach_form($this->system_form->get_search_currency_form());
        $this->load->view('view_currency_list', $data);
    }

    function currency_list_json()
    {
        $json_data = array();

        $count_all = $this->system_model->getcurrency_list(false);
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->system_model->getcurrency_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->system_form->build_currency_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
    }

    function currency_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {
            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);

            $this->session->set_userdata('currency_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'systems/currency_list/');
        }
    }

    function currency_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('currency_search', "");
    }

    function currency_add()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = gettext('Add Currency');
        $data['form'] = $this->form->build_form($this->system_form->get_currency_form_fields(), '');
        $this->load->view('view_currency_add_edit', $data);
    }

    function currency_list_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Currency');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "currency", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $data['form'] = $this->form->build_form($this->system_form->get_currency_form_fields($edit_id), $edit_data);
        $this->load->view('view_country_add_edit', $data);
    }

    function currency_save()
    {
        $add_array = $this->input->post();
        $data['form'] = $this->form->build_form($this->system_form->get_currency_form_fields($add_array['id']), $add_array);
        if ($add_array['id'] != '') {
            $data['page_title'] = gettext('Edit Currency');
            $data['page_title'] = gettext('Edit Currency');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $this->system_model->edit_currency($add_array, $add_array['id']);
                echo json_encode(array(
                    "SUCCESS" => gettext(sprintf('%s currency updated successfully!', $add_array["currencyname"]))
                ));
                exit();
            }
        } else {
            $data['page_title'] = gettext('Create Currency');
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $response = $this->system_model->add_currency($add_array);
                echo json_encode(array(
                    "SUCCESS" => gettext(sprintf('%s  currency added successfully!', $add_array["currencyname"]))
                ));
                exit();
            }
        }
    }

    function currency_remove($id)
    {
        $currencyname = $this->common->get_field_name('currencyname', 'currency', $id);
        $this->system_model->remove_currency($id);
        $this->session->set_flashdata('astpp_notification', gettext(sprintf('%s  Currency removed successfully!', $currencyname)));

        redirect(base_url() . 'systems/currency_list/');
    }

    function currency_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        echo $this->db->delete("currency");
    }

    function database_backup()
    {
        $data = array();
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Database Backup');
        $filename = $this->db->database . "_" . date("YmdHms") . ".sql.gz";
        $data['form'] = $this->form->build_form($this->system_form->get_backup_database_form_fields($filename), '');
        $this->load->view('view_database_backup', $data);
    }

    function database_backup_save()
    {
        $add_array = $this->input->post();

        $data['form'] = $this->form->build_form($this->system_form->get_backup_database_form_fields($add_array['path'], $add_array['id']), $add_array);
        $data['page_title'] = gettext('Database Backup');
        if ($add_array['id'] != '') {} else {
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                echo $data['validation_errors'];
                exit();
            } else {
                $db_name = $this->db->database;
                $db_username = $this->db->username;
                $db_password = $this->db->password;
                $db_hostname = $this->db->hostname;
                $filename = $add_array['path'];
                $backup_file = DATABASE_DIRECTORY . $filename;
                if (substr($backup_file, - 3) == '.gz') {
                    $backup_file = substr($backup_file, 0, - 3);
                    $do_gzip = 1;
                }

                $run_backup = "/usr/bin/mysqldump -all --databases " . $db_name . " -u'" . $db_username . "' -p'" . $db_password . "' > '$backup_file'";
                $error_zip = 0;
                exec($run_backup, $output, $error);
                if ($do_gzip) {
                    $gzip = $this->config->item('gzip-path');
                    $run_gzip = $gzip . " " . $backup_file;
                    exec($run_gzip, $output, $error_zip);
                }

                if ($error == 0 && $error_zip == 0) {
                    $this->system_model->backup_insert($add_array);
                    echo json_encode(array(
                        "SUCCESS" => gettext(sprintf('%s  backup exported successfully!', $add_array['backup_name']))
                    ));

                    exit();
                } else {
                    echo gettext('An error occur when the system tried to backup of the database. Please check yours system settings for the backup section');
                    exit();
                }
            }
        }
    }

    function database_restore()
    {
        $data['page_title'] = gettext('Database Backup');
        $data['grid_fields'] = $this->system_form->build_backupdastabase_list();
        $data["grid_buttons"] = $this->system_form->build_backupdastabase_buttons();
        $this->load->view('view_database_list', $data);
    }

    function database_restore_json()
    {
        $json_data = array();
        $count_all = $this->system_model->getbackup_list(false, "", "");
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->system_model->getbackup_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->system_form->build_backupdastabase_list());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);

        echo json_encode($json_data);
        exit();
    }

    function database_restore_one($id = '')
    {
        $result = $this->system_model->get_backup_data($id);
        $result_array = $result->result_array();
        if ($result->num_rows() > 0) {
            $db_name = $this->db->database;
            $db_username = $this->db->username;
            $db_password = $this->db->password;
            $db_hostname = $this->db->hostname;
            $path = DATABASE_DIRECTORY . $result_array[0]['path'];
            if (file_exists($path)) {
                if (substr($path, - 3) == '.gz') {
                    $GUNZIP_EXE = $this->config->item('gunzip-path');
                    $run_gzip = $GUNZIP_EXE . " < " . $path . " | ";
                }
                $MYSQL = "/usr/bin/mysql";
                $run_restore = $run_gzip . $MYSQL . " -h" . $db_hostname . " -u" . $db_username . " -p" . $db_password . " " . $db_name;
                exec($run_restore);
                $this->session->set_flashdata('astpp_errormsg', gettext('Database Restore successfully.'));
                redirect(base_url() . 'systems/database_restore/');
            } else {
                $this->session->set_flashdata('astpp_notification', gettext('File not exists!'));
                redirect(base_url() . 'systems/database_restore/');
            }
        }
        redirect(base_url() . 'systems/database_restore/');
    }

    function database_download($id = '')
    {
        $result = $this->system_model->get_backup_data($id);
        $result_array = $result->result_array();
        if ($result->num_rows() > 0) {
            $path = DATABASE_DIRECTORY . $result_array[0]['path'];
            $filename = basename($path);
            $len = filesize($path);

            header("Content-Encoding: binary");
            header("Content-Type: application/octet-stream");
            header("content-length: " . $len);
            header("content-disposition: attachment; filename=" . $filename);
            header("Expires: 0");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Cache-Control: private");
            header("Pragma: public");
            ob_clean();
            $fp = fopen($path, "r");
            fpassthru($fp);
            exit();
        }
    }

    function database_import()
    {
        $data['page_title'] = gettext('Import Database');
        $this->load->view('view_import_database', $data);
    }

    function database_import_file()
    {
        $filename_text = $_POST['fname'];
        $upload_greeting_file = $_FILES['userfile']['name'];
        $filename = DATABASE_DIRECTORY . $upload_greeting_file;
        if (file_exists($filename)) {
            $this->session->set_flashdata('astpp_notification', "This file " . basename($_FILES['userfile']['name']) . " is exists.");
            redirect(base_url() . "systems/database_restore/");
        } else {
            $db_file = explode(".", $upload_greeting_file);
            if ($db_file[1] == 'csv' || $db_file[1] == 'tar' || $db_file[1] == 'sql') {
                $target_path = basename($_FILES['userfile']['name']);
                move_uploaded_file($_FILES["userfile"]["tmp_name"], $target_path);

                $query = $this->system_model->import_database($filename_text, $_FILES['userfile']['name']);
                $this->session->set_flashdata('astpp_errormsg', "The file " . basename($_FILES['userfile']['name']) . " has been uploaded");
                redirect(base_url() . "systems/database_restore/");
            } else {
                $this->session->set_flashdata('astpp_notification', gettext("There is a some issue or invalid file format."));
                redirect(base_url() . "systems/database_restore/");
            }
        }
    }

    function database_delete($id)
    {
        $where = array(
            'id' => $id
        );
        $this->db->where($where);
        $this->db->delete("backup_database");

        $this->session->set_flashdata('astpp_errormsg', gettext('Database backup deleted successfully.'));
        redirect(base_url() . 'systems/database_restore/');
        return true;
    }

    function database_backup_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        echo $this->db->delete("backup_database");
    }

    function languages_list()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['page_title'] = gettext('Languages');
        $data['search_flag'] = true;
        $data['cur_menu_no'] = 4;
        $this->session->set_userdata('languages_search', 0);
        $data['grid_fields'] = $this->system_form->build_languages_list_for_admin();

        if ($this->session->userdata('logintype') == 2) {
            $data["grid_buttons"] = $this->system_form->build_admin_languages_grid_buttons();
        } else {
            $data["grid_buttons"] = json_encode(array());
        }

        $data['form_search'] = $this->form->build_serach_form($this->system_form->get_search_languages_form());
        $this->load->view('view_languages_list', $data);
    }

    function languages_list_json()
    {
        $json_data = array();
        $count_all = $this->system_model->getlanguages_list(false, "", "");
        $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
        $json_data = $paging_data["json_paging"];

        $query = $this->system_model->getlanguages_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
        $grid_fields = json_decode($this->system_form->build_languages_list_for_admin());
        $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        echo json_encode($json_data);
    }

    function languages_list_search()
    {
        $ajax_search = $this->input->post('ajax_search', 0);

        if ($this->input->post('advance_search', TRUE) == 1) {

            $this->session->set_userdata('advance_search', $this->input->post('advance_search'));
            $action = $this->input->post();
            unset($action['action']);
            unset($action['advance_search']);
            $this->session->set_userdata('currency_list_search', $action);
        }
        if (@$ajax_search != 1) {
            redirect(base_url() . 'systems/currency_list/');
        }
    }

    function languages_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('currency_search', "");
    }

    function languages_list_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Languages');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "languages", $where);
        foreach ($account->result_array() as $key => $value) {
            $edit_data = $value;
        }
        $data['form'] = $this->form->build_form($this->system_form->get_languages_form_fields($edit_data['id']), $edit_data);
        $this->load->view('view_languages_edit', $data);
    }

    function languages_add()
    {
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $data['page_title'] = gettext('Add Languages');
        $data['form'] = $this->form->build_form($this->system_form->get_languages_form_fields(), '');
        $this->load->view('view_languages_add_edit', $data);
    }

    function languages_remove($id)
    {
        $languagename = $this->common->get_field_name('*', 'languages', $id);
        $this->system_model->remove_languages($id);
        $this->session->set_flashdata('astpp_notification', gettext(sprintf('%s Languages removed successfully!', $languagename)));

        redirect(base_url() . 'systems/languages_list/');
    }

    function languages_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $data = explode(',', $ids);

        foreach ($data as $key => $value) {
            $value = str_replace("'", "", $value);
            $new_array[] = $value;
        }
        foreach ($new_array as $key => $value) {
            $query = $this->db->get_where('languages', array(
                'id' => $value
            ));
            $localedata = $query->first_row();
            $localename = $localedata->locale;
            if ($this->db->field_exists($localename, 'translations')) {
                $this->db->query('ALTER TABLE translations DROP ' . $localename . ' ');
            } else {
                redirect(base_url() . 'systems/languages_list/');
                exit();
            }
        }
        $this->db->where($where);
        echo $this->db->delete("languages");
    }

    function languages_save()
    {
        $add_array = $this->input->post();
        $response = array();
        foreach ($add_array as $key => $value) {
            if ($value == 'id' || $value == 'ID' || $value == 'Id' || $value == 'iD') {
                $response[$key . "_error"] = $key . " Field is not valid";
            }
        }
        $data['form'] = $this->form->build_form($this->system_form->get_languages_form_fields($add_array['id']), $add_array);
        if ($add_array['id'] != '') {
            $data['page_title'] = gettext('Edit Languages');
            if ($this->form_validation->run() == FALSE || ! empty($response)) {
                $data['validation_errors'] = validation_errors();
                if (! empty($response)) {
                    if (! empty($data['validation_errors'])) {
                        $json_decode = json_decode($data['validation_errors'], true);
                        $data['validation_errors'] = json_encode(array_merge($json_decode, $response));
                    } else {
                        $data['validation_errors'] = json_encode($response);
                    }
                }
                echo $data['validation_errors'];
                exit();
            } else {
                $this->system_model->edit_languages($add_array, $add_array['id']);
                echo json_encode(array(
                    "SUCCESS" => gettext(sprintf('%s languages updated successfully!', $add_array["name"]))
                ));
                exit();
            }
        } else {
            $data['page_title'] = gettext('Create Languages');
            if ($this->form_validation->run() == FALSE || ! empty($response)) {
                $data['validation_errors'] = validation_errors();
                if (! empty($response)) {
                    if (! empty($data['validation_errors'])) {
                        $json_decode = json_decode($data['validation_errors'], true);
                        $data['validation_errors'] = json_encode(array_merge($json_decode, $response));
                    } else {
                        $data['validation_errors'] = json_encode($response);
                    }
                }
                echo $data['validation_errors'];
                exit();
            } else {
                $add_array['name'] = str_replace(' ', '_', trim($add_array['name']));
                $add_array['locale'] = str_replace(' ', '_', trim($add_array['locale']));
                $add_array['code'] = str_replace(' ', '_', trim($add_array['code']));
                $response = $this->system_model->add_languages($add_array);
                echo json_encode(array(
                    "SUCCESS" => gettext(sprintf('%s languages added successfully!', $add_array["name"]))
                ));
                exit();
            }
            $this->load->view('view_languages_add_edit', $data);
        }
    }

    function translation_list()
    {
        $data['accountinfo'] = $this->session->userdata("accountinfo");
        $data['page_title'] = gettext('Translations');
        if (isset($_POST['id']) && ! empty($_POST['id'])) {
            $this->session->set_userdata("opting_translation", $_POST['id']);
            $data['grid_fields'] = $this->system_form->build_translation_list_for_admin_translation();
        } else {
            $data['grid_fields'] = $this->system_form->build_translation_list_for_admin();
            $data["grid_buttons"] = $this->system_form->translation_build_grid_buttons();
        }
        $this->load->view('view_translation_list', $data);
    }

    function translation_list_json()
    {
        $json_data = array();
        $opting_id = $this->session->userdata("opting_process");
        if ($opting_id != '') {
            $count_res = $this->system_model->getproduct_list(false);
            $count_all = (array) $count_res->first_row();
            $paging_data = $this->form->load_grid_config($count_all['count'], $_GET['rp'], $_GET['page']);
            $json_data = $paging_data["json_paging"];
            $query = $this->system_model->getproduct_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
            $grid_fields = json_decode($this->system_form->build_translation_list_for_admin_translation());
            $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
            $this->session->unset_userdata('opting_process');
        } else {
            $count_all = $this->system_model->get_translation_list(false);
            $paging_data = $this->form->load_grid_config($count_all, $_GET['rp'], $_GET['page']);
            $json_data = $paging_data["json_paging"];
            $query = $this->system_model->get_translation_list(true, $paging_data["paging"]["start"], $paging_data["paging"]["page_no"]);
            $grid_fields = json_decode($this->system_form->build_translation_list_for_admin());
            $json_data['rows'] = $this->form->build_grid($query, $grid_fields);
        }
        echo json_encode($json_data);
    }

    function translation_list_edit($edit_id = '')
    {
        $data['page_title'] = gettext('Edit Translation Language');
        $where = array(
            'id' => $edit_id
        );
        $account = $this->db_model->getSelect("*", "translations", $where);
        foreach ($account->result_array() as $key => $value) {
            $data['edit_data'] = $value;
        }
        $this->load->view('view_translation_edit', $data);
    }

    function translation_add()
    {
        $data['page_title'] = gettext('Create Translation Languages');
        $data['username'] = $this->session->userdata('user_name');
        $data['flag'] = 'create';
        $fields_data = $this->db->list_fields('translations');
        unset($fields_data['0']);
        $this->db->from('languages');
        $query = $this->db->get();
        $query = $query->result_array();
        $this->load->view('view_translation_add', $data);
    }

    function translation_remove($id)
    {
        $translation_info = $this->common->get_field_name('*', 'translations', $id);
        $this->system_model->remove_translation($id);
        $this->session->set_flashdata('astpp_notification', gettext(sprintf('%s Translation removed successfully!', $translation_info)));

        redirect(base_url() . 'systems/translation_list/');
    }

    function translation_delete_multiple()
    {
        $ids = $this->input->post("selected_ids", true);
        $where = "id IN ($ids)";
        $this->db->where($where);
        echo $this->db->delete("translations");
    }

    function translation_save()
    {
        $add_array = $this->input->post();
        if (isset($add_array) && count($add_array) > 0) {
            foreach ($add_array as $key => $val) {
                if ($key != "id" && $key != "action") {
                    if ($key == "module_name") {
                        $display_name = str_replace("_", " ", $key);
                        $display_name = ucwords($display_name);
                    } else {
                        $display_name = $this->common->get_field_name('name', 'languages', array(
                            'locale' => $key
                        ));
                    }
                    $this->form_validation->set_rules($key, $display_name, 'required|trim|xss_clean');
                }
            }
        }
        if (isset($add_array['id']) && $add_array['id'] != '') {
            if ($this->form_validation->run() == FALSE) {
                $data['page_title'] = gettext('Edit Translation');
                $data['flag'] = true;
                $data['edit_data'] = $add_array;
                $data['validation_errors'] = validation_errors();
                $this->load->view('view_translation_edit', $data);
            } else {
                $response = $this->system_model->edit_translation($add_array, $add_array['id']);
                $this->session->set_flashdata('astpp_errormsg', ucfirst($add_array["module_name"]) . ' translation updated successfully!');
                redirect(base_url() . 'systems/translation_list/');
            }
        } else {
            if ($this->form_validation->run() == FALSE) {
                $data['validation_errors'] = validation_errors();
                $data['page_title'] = gettext('Create Translation');
                $data['flag'] = true;
                $data['details'] = $add_array;
                $this->load->view('view_translation_add', $data);
            } else {
                $data['page_title'] = gettext('Create Translation');
                $response = $this->system_model->add_translation($add_array);
                $this->session->set_flashdata('astpp_errormsg', ucfirst($add_array["module_name"]) . ' translation added successfully!');
                redirect(base_url() . 'systems/translation_list/');
            }
        }
    }

    function translation_list_clearsearchfilter()
    {
        $this->session->set_userdata('advance_search', 0);
        $this->session->set_userdata('translation_list', "");
    }

    function payment_methods()
    {
        $group_title = 'payment_methods';
        if ($group_title == "") {
            redirect(base_url() . '/dashboard');
        }
        if ($group_title == "email") {
            $data['test_email_flag'] = true;
        }
        $data['username'] = $this->session->userdata('user_name');
        if ($group_title == 'payment_methods') {
            $page_title = str_replace("_", " ", $group_title);
            $data['page_title'] = ucwords($page_title);
        } else {
            $data['page_title'] = gettext(ucfirst($group_title));
        }
        $data['group_title'] = $group_title;

        $data['details'] = $this->system_model->get_subcategory_menu($group_title);
        $add_array = $this->input->post();
        if (! empty($add_array)) {
            if ($add_array['tax_type'] != '') {
                $selected_tax = implode(",", $add_array['tax_type']);
                $add_array['tax_type'] = $selected_tax;
            }
            if (isset($add_array['version'])) {
                unset($add_array['version']);
            }

            foreach ($add_array as $key => $val) {
                $update_array = array(
                    'value' => $val
                );

                $this->system_model->edit_configuration($update_array, $key);
            }
            $this->session->set_flashdata('astpp_errormsg', gettext(sprintf('%s  Settings updated sucessfully!', ucfirst($group_title))));

            redirect(base_url() . 'systems/configuration/' . $group_title);
        } else {
            $data['menu'] = $this->system_model->get_system_sidemenue();
            $this->load->view('view_systemconf', $data);
        }
    }
}
