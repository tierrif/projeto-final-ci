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
        $this->load->model('')
    }

    public function index() {
        

        // Carregar template.
        $this->renderer->render('contacto', ['recaptcha_public_key' => RECAPTCHA_PUBLIC_KEY]);
    }
    
    public function submit() {
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

            $this->renderer->render('contacto_success', []);
        } else {
            // Erro, o CAPTCHA é inválido. Normalmente, isto não deve acontecer, porque o CAPTCHA pertence às regras do form.
            $this->session->set_flashdata('alertType', 'alert-danger');
            $this->session->set_flashdata('alertMessage', 'O CAPTCHA é inválido. Por favor, tente novamente.');
            // Tentar novamente.
            redirect(base_url('Contacto'));
        }
    }
}
