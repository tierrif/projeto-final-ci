<? defined('BASEPATH') or exit('No direct script access allowed');

class Mensagens extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['serverConfig', 'adapter', 'util', 'form']);
        $this->load->library(['pagination', 'form_validation']);
        $this->load->model('mensagemModel');
        if (!$this->authModel->isLoggedIn() || !$this->authModel->hasPermission('customer-support')) {
            redirect(base_url('NoAccess'));
        }
    }

    /*
     * Index - Listagem de todas
     * as mensagens.
     */
    public function index() {
        // Obter a página atual.
        $page = ($this->uri->segment(URI_SEGMENT)) ? $this->uri->segment(URI_SEGMENT) : 0;
        // Configuração da paginação.
        $config['base_url'] = base_url('Mensagens/index');
        $config['total_rows'] = $this->mensagemModel->getCount();
        $config['per_page'] = PAGE_NUM_OF_ROWS; // helpers/ServerConfig_helper.php.
        $config['uri_segment'] = URI_SEGMENT; // helpers/ServerConfig_helper.php.
        // Inicializar a paginação.
        $this->pagination->initialize($config);
        $data['mensagens'] = (new MensagemAdapter)->adapt($this->mensagemModel->getAllWithDetails($config['per_page'], $page));
        $data['pagination'] = $this->pagination->create_links();

        // Carregar template.
        $this->renderer->render('admin/mensagens', $data, true, true);
    }

    public function view($id) {
      // Mensagem foi vista.
      $this->mensagemModel->update([
        'id' => $id,
        'vista' => 1
      ]);

      // Obter mensagem por ID.
      $mensagem = $this->mensagemModel->getById($id);
      
      $this->renderer->render('mensagens_view', [
        'nome' => $mensagem['nome'],
        'email' => $mensagem['email'],
        'mensagem' => $mensagem['mensagem'],
        'del_uri' => base_url('Mensagens/delete/' . $id)
      ], true, true);
    }

    protected function onDelete($id) {
        // Elimina.
        $this->mensagemModel->delete($id);
    }

    
}
