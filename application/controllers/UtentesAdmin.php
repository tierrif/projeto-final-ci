<? defined('BASEPATH') or exit('No direct script access allowed');

class UtentesAdmin extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['login', 'serverConfig', 'adapter', 'util', 'form']);
        $this->load->library(['pagination', 'form_validation']);
        $this->load->model('utenteModel');
        if (!isLoggedIn() || !hasPermission('admin', $this->session)) {
            redirect(base_url('noaccess'));
        }
    }

    /*
     * Index - Listagem de todos
     * os utentes.
     */
    public function index() {
        // Obter a página atual.
        $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;
        // Configuração da paginação.
        $config['base_url'] = base_url('UtentesAdmin/index');
        $config['total_rows'] = $this->utenteModel->getCount();
        $config['per_page'] = PAGE_NUM_OF_ROWS; // helpers/ServerConfig_helper.php.
        $config['uri_segment'] = URI_SEGMENT; // helpers/ServerConfig_helper.php.
        // Inicializar a paginação.
        $this->pagination->initialize($config);
        $data['utentes'] = (new UtenteAdminAdapter)->adapt($this->utenteModel->getAllWithMoradaAndConsultas($config['per_page'], $page));
        $data['pagination'] = $this->pagination->create_links();
        $data['utente_form'] = $this->renderer->manualRender('details/utente', [
            'morada_form_include' => $this->renderer->manualRender('includes/morada_form', []),
            'action_uri' => base_url('UtentesAdmin/details/-1/insert')
        ]);

        // Carregar template.
        $this->renderer->render('admin/utentes', $data, true, true);
    }

    protected function onDelete($id) {
        // Elimina.
        $this->utenteModel->deleteAlongMorada($id);
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
                'nome_value' => set_value('nome'),
                'num_utente_value' => set_value('numutente'),
                'morada_form_include' => $this->renderer->manualRender('includes/morada_form', [
                    'id_morada' => (set_value('idmorada') ? set_value('idmorada') : $utente['idMorada']),
                    'morada_linha_1_value' => set_value('morada'),
                    'morada_linha_2_value' => set_value('morada2'),
                    'cidade_value' => set_value('cidade'),
                    'estado_value' => set_value('estado'),
                    'codigo_postal_value' => set_value('codpostal')
                ])
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
            return $id;
        }

        // Inserir dados pelo model.
        $moradaId = $this->utenteModel->addMorada($morada);
        $utente['idMorada'] = $moradaId;
        $newId = $this->utenteModel->add($utente);

        // Retornar o ID.
        return $newId;
    }

    protected function getTemplateName() {
        return 'utente';
    }

    protected function temporaryData() {
        return [
            'nome' => 'Vazio ' . date('d/m/yy H:i:s')
        ];
    }
}
