<?php defined('BASEPATH') OR exit('No direct script access allowed');


class Invoices extends CI_Controller
{
    function __construct()
    {
        set_time_limit(1000);
        parent::__construct();

        $this->load->database();
        $this->load->helper('url');
        $this->load->library('grocery_CRUD');
        $this->load->helper('general_helper');

        if (!is_user_logged_in()) {
            redirect('login');
        }

        $this->template->write_view('sidenavs', 'template/default_sidenavs', true);
        $this->template->write_view('navs', 'template/default_topnavs.php', true);
    }

    function index()
    {
        $this->template->write('title', 'Invoices', TRUE);
        $this->template->write('header', 'Invoices');
        $this->template->write('javascript', '
            $(function() {
                setColor();
                $(document).ajaxComplete(function() {
                    setColor();
                });
            });
            function setColor() {
                $(".new").parent().parent().css("background-color", "darkorange");
                $(".sent").parent().parent().css("background-color", "forestgreen");
                $(".paid").parent().parent().css("background-color", "royalblue");
                $(".cancelled").parent().parent().css("background-color", "darkred");
            }
        ');

        $crud = new grocery_CRUD();
        $crud->set_table('invoices');
        $crud->set_subject('Invoice');
        $crud->unset_add();

        $fields = ['invoice_client', 'invoice_amount', 'invoice_tva', 'invoice_date', 'invoice_status', 'invoice_file_link', 'tva_invoice_file_link'];
        $crud->columns($fields);

        if ('edit' == $crud->getState() || 'update_validation' == $crud->getState() || 'update' == $crud->getState()) {
            $fields = ['invoice_tva', 'invoice_status'];
        }

        $crud->set_relation('invoice_client', 'customers', 'customerName');
        $crud->fields($fields);
        $crud->field_type('invoice_status', 'dropdown', array_combine($this->config->item('STATUS'), $this->config->item('STATUS')));
        $crud->field_type('invoice_tva', 'true_false');
        $crud->required_fields($fields);
        $crud->set_field_upload('invoice_file_link', 'assets/pdfs');
        $crud->set_field_upload('tva_invoice_file_link', 'assets/pdfs');
        $crud->display_as('invoice_number', 'Invoice Number')
            ->display_as('invoice_client', 'Client')
            ->display_as('invoice_amount', 'Amount')
            ->display_as('invoice_tva', 'TVA?')
            ->display_as('invoice_date', 'Invoice Date')
            ->display_as('invoice_status', 'Status')
            ->display_as('invoice_file_link', 'File Link')
            ->display_as('tva_invoice_file_link', 'TVA File Link');

        $crud->callback_column('invoice_status', array($this, 'set_color'));

        $this->template->write_view('content', 'example', $crud->render());
//    highlight_string("<?php\n".var_export($crud, true));
//    die();
        $this->template->render();
    }

    function set_color($value = '', $row)
    {
        $class = "";
        switch ($value) {
            case "New":
                $class = "new";
                break;
            case "Sent":
                $class = "sent";
                break;
            case "Paid":
                $class = "paid";
                break;
            case "Cancelled":
                $class = "cancelled";
                break;
        }

        return "<span style='color: white;' class='$class'>$value</span>";
    }

    function set_default_status($value = '', $primary_key = null)
    {
        return form_dropdown(
            'invoice_status',
            array_combine($this->config->item('STATUS'), $this->config->item('STATUS')),
            $this->config->item('DEFAULT_STATUS'),
            [
                'id' => 'field-status',
                'class' => 'chosen-select'
            ]
        );
    }
}
