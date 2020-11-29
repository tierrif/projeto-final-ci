<? defined('BASEPATH') or exit('No direct script access allowed');

class Login extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->model('authModel');
        $this->load->library('form_validation');
    }

    public function index($fromForm = null) {
        if ($this->authModel->isLoggedIn()) {
            // Login desnecessário.
            redirect(base_url('Base'));
        }
        $data = [
            'login_base_url' => base_url('Login/index/fromForm'),
            'username_value' => set_value('username'),
        ];
        if ($fromForm) {
            $this->form_validation->set_rules('username', 'username', 'required');
            $this->form_validation->set_rules('password', 'password', 'required');
            // Eliminar tags <p> do validation_errors().
            $this->form_validation->set_error_delimiters('', '');
            if ($this->form_validation->run()) {
                if ($this->authModel->checkPassword($this->input->post('username'), $this->input->post('password'))) {
                    $this->authModel->createSession($this->input->post('username'));
                    return redirect(base_url('Base'));
                }

                $data['alert'] = $this->renderer->manualRender('includes/form_alert', [
                    'alert_type' => 'alert-danger',
                    'alert_message' => 'Username ou password errado(s).'
                ]);
            } else {
                $data['alert'] = $this->renderer->manualRender('includes/form_alert', [
                    'alert_type' => 'alert-danger',
                    'alert_message' => validation_errors()
                ]);
            }
        }
        $this->renderer->render('login', $data);
    }

    public function logout() {
        $this->session->unset_userdata('token');
        redirect(base_url('Base'));
    }
}