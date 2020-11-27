<? defined('BASEPATH') or exit('No direct script access allowed');

/*
 * Controlador contacto:
 * Onde o utilizador pode
 * criar um formulário de
 * contacto para ser ajudado.
 */
class Contacto extends MY_Controller {
    public function __construct() {
        // Construtor-pai.
        parent::__construct();
        $this->load->model('contactoModel');
        $this->load->library('form_validation');
    }

    public function index() {
        // Regras do form.
        $this->form_validation->set_rules('nome', 'nome', 'required');
        $this->form_validation->set_rules('email', 'email', 'required');
        $this->form_validation->set_rules('mensagem', 'mensagem', 'required');
        $this->form_validation->set_rules('g-recaptcha-response', 'CAPTCHA', 'required');

        $data = [
            'recaptcha_public_key' => RECAPTCHA_PUBLIC_KEY,
            'nome' => $this->input->post('nome'),
            'email' => $this->input->post('email'),
            'mensagem' => $this->input->post('mensagem')
        ];
        // Eliminar tags <p> do validation_errors().
        $this->form_validation->set_error_delimiters('', '');

        // Verificar se o form é válido.
        if ($this->form_validation->run() && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // Submeter.
            $this->submit($data);
            return;
        } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data['alert'] = $this->renderer->manualRender('includes/form_alert', [
                'alert_type' => 'alert-danger',
                'alert_message' => validation_errors()
            ]);
        }
        
        // Carregar template.
        $this->renderer->render('contacto', $data);
    }
    
    protected function submit($req) {
        // Resposta do form do captcha.
        $recaptchaReponse = $this->input->post('g-recaptcha-response');
      
	    // URL para a API do reCAPTCHA.
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        // Dados adicionais para o pedido.
        $data = array('secret' => RECAPTCHA_PRIVATE_KEY, 'response' => $recaptchaReponse);

        // CURL - Criar pedidos a partir do servidor.
	    $curl = curl_init(); // Inicializar.
	    curl_setopt($curl, CURLOPT_URL, $url); // Passar o URL.
	    curl_setopt($curl, CURLOPT_POST, true); // Pedido em POST.
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Retornar o resultado.
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Dados adicionais.

        // Fazer o pedido.
        $response = curl_exec($curl);
        
        // Fechar o CURL.
        curl_close($curl);

        // Resposta em JSON descodificada.
        $responseStatus = json_decode($response, true); // true: passar em array associativo.
        if (arrayValue($responseStatus, 'success')) {
            $this->contactoModel->add([
                'nome' => $this->input->post('nome'),
                'email' => $this->input->post('email'),
                'mensagem' => $this->input->post('mensagem')
            ]);
            $this->renderer->render('contacto_success', ['nome' => $this->input->post('nome')]);
        } else {
            // Erro, o CAPTCHA é inválido. Normalmente, isto não deve acontecer, porque o CAPTCHA pertence às regras do form.
            $this->session->set_flashdata('alertType', 'alert-danger');
            $this->session->set_flashdata('alertMessage', 'O CAPTCHA é inválido. Por favor, tente novamente.');
            // Tentar novamente.
            $this->renderer->render('contacto', $req);
        }
    }
}
