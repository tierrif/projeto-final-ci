<? defined('BASEPATH') or exit('No direct script access allowed');

class UtentesAdmin extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['login', 'serverConfig', 'adapter', 'util', 'form']);
        $this->load->library(['pagination', 'form_validation', 'session']);
        $this->load->model('utenteModel');
        if (!isLoggedIn()) {
            redirect(base_url('noaccess'));
        }
    }

    /*
     * Index - Listagem de todos
     * os utentes.
     */
    public function index() {
        // Obter a página atual.
        $page = ($this->uri->segment(2)) ? $this->uri->segment(2) : 0;
        // Configuração da paginação.
        $config['base_url'] = base_url('utentesAdmin');
        $config['total_rows'] = $this->utenteModel->getCount();
        $config['per_page'] = PAGE_NUM_OF_ROWS; // helpers/ServerConfig_helper.php.
        $config['uri_segment'] = URI_SEGMENT; // helpers/ServerConfig_helper.php.
        // Inicializar a paginação.
        $this->pagination->initialize($config);
        $data['utentes'] = (new UtenteAdminAdapter)->adapt($this->utenteModel->getAllWithMoradaAndConsultas($config['per_page'], $page));
        $data['pagination'] = $this->pagination->create_links();

        // Carregar template.
        $this->renderer->render('admin/utentes', $data, true, true);
    }

    public function delete($id = 0) {
        // Se o ID não for passado (/delete, em vez de /delete/:id), redireciona.
        if (!$id) {
            redirect(base_url('utentesAdmin'));
            return;
        }


    }

    /*
     * Details - Detalhes de um
     * utente específico.
     */
    protected function onDetailsRender($id, $fromForm) {
        // Dados dinâmicos a renderizar no mustache.
        $utente = $this->utenteModel->getById($id);
        // Se o pedido for feito pelo form, em POST, não setar $data da base de dados.
        if (!$fromForm) {
            $data = (new UtenteDetailsAdapter)->adapt($utente);
            $data['morada_form_include'] = $this->renderer->manualRender('includes/morada_form',
            (new MoradaDetailsAdapter)->adapt($this->utenteModel->getMoradaById($utente['idMorada'])));
        } else {
            $data = [
                'nome_value' => $this->input->post('nome'),
                // TODO: Adicionar o resto dos dados.
            ];
        }

        return $data;
    }

    protected function formElements() {
        return [
            [
                'field' => 'nome',
                'label' => 'Nome',
                'rules' => 'required'
            ],
            [
                'field' => 'numutente',
                'label' => 'Número de Utente',
                'rules' => 'required|numeric'
            ],
            [
                'field' => 'morada',
                'label' => 'Morada (linha 1)',
                'rules' => 'required'
            ],
            [
                'field' => 'morada2',
                'label' => 'Morada (linha 2)',
                'rules' => 'none'
            ],
            [
                'field' => 'cidade',
                'label' => 'Cidade',
                'rules' => 'required'
            ],
            [
                'field' => 'estado',
                'label' => 'Estado/Distrito',
                'rules' => 'required'
            ],
            [
                'field' => 'codpostal',
                'label' => 'Código Postal',
                'rules' => 'required'
            ]
        ];
    }

    protected function handleDatabaseCalls($id) {
        // Arrays associativos que funcionam tanto para UPDATE como INSERT.
        $utente = [
            'id' => ($id > 0 ? $id : null), // Ao inserir, se ID é nulo, auto-incrementa na BD.
            'nome' => $this->input->post('nome'),
            'nUtente' => $this->input->post('numutente')
        ];
        $morada = [
            'id' => ($id > 0 ? $this->input->post('idmorada') : null),
            'firstLine' => $this->input->post('morada'),
            'secondLine' => $this->input->post('morada2'),
            'zipCode' => $this->input->post('codpostal'),
            'state' => $this->input->post('estado'),
            'city' => $this->input->post('cidade')
        ];

        // Verificar se o utente existe. Se sim, atualizar dados.
        if ($id > 0) {
            // Atualizar dados pelo model.
            $this->utenteModel->update($utente);
            $this->utenteModel->updateMorada($morada);
            // Já atualizámos, o código seguinte apenas serve para inserir.
            return;
        }

        // Inserir dados pelo model.
        $this->utenteModel->add($utente);
        $this->utenteModel->addMorada($morada);
    }

    protected function getTemplateName() {
        return 'utente';
    }
}
