<? defined('BASEPATH') or exit('No direct script access allowed');

class MedicosAdmin extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['serverConfig', 'adapter', 'util', 'form']);
        $this->load->library(['pagination', 'form_validation']);
        $this->load->model('medicoModel');
        if (!$this->authModel->isLoggedIn() || !$this->authModel->hasPermission('manage-entities')) {
            redirect(base_url('NoAccess'));
        }
    }

    /*
     * Index - Listagem de todos
     * os enfermeiros.
     */
    public function index() {
        // Obter a página atual.
        $page = ($this->uri->segment(URI_SEGMENT)) ? $this->uri->segment(URI_SEGMENT) : 0;
        // Configuração da paginação.
        $config['base_url'] = base_url('MedicosAdmin/index');
        $config['total_rows'] = $this->medicoModel->getCount();
        $config['per_page'] = PAGE_NUM_OF_ROWS; // helpers/ServerConfig_helper.php.
        $config['uri_segment'] = URI_SEGMENT; // helpers/ServerConfig_helper.php.
        // Inicializar a paginação.
        $this->pagination->initialize($config);
        $data['medicos'] = (new MedicoAdminAdapter)->adapt($this->medicoModel->getAllWithMorada($config['per_page'], $page, 'MedicosAdmin'));
        $data['pagination'] = $this->pagination->create_links();
        $data['medico_form'] = $this->renderer->manualRender('details/medico', [
            'morada_form_include' => $this->renderer->manualRender('includes/morada_form', []),
            'action_uri' => base_url('MedicosAdmin/details/-1/insert')
        ]);

        // Carregar template.
        $this->renderer->render('admin/medicos', $data, true, true);
    }

    protected function onDelete($id) {
        // Elimina.
        $this->medicoModel->deleteAlongMorada($id);
    }

    /*
     * Details - Detalhes de um
     * médico específico.
     */
    protected function onDetailsRender($id, $fromForm) {
        // Dados dinâmicos a renderizar no mustache.
        $medico = $this->medicoModel->getById($id);
        // Se o pedido for feito pelo form, em POST, não setar $data da base de dados.
        if (!$fromForm) {
            $data = (new MedicoDetailsAdapter)->adapt($medico);
            $data['morada_form_include'] = $this->renderer->manualRender('includes/morada_form',
            (new MoradaDetailsAdapter)->adapt($this->medicoModel->getMoradaById($medico['idMorada'])));
        } else {
            $data = [
                'nome_value' => set_value('nome'),
                'especialidade_value' => set_value('especialidade'),
                'nif_value' => set_value('nif'),
                'nib_value' => set_value('nib'),
                'morada_form_include' => $this->renderer->manualRender('includes/morada_form', [
                    'id_morada' => (set_value('idmorada') ? set_value('idmorada') : $medico['idMorada']),
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
                'field' => 'especialidade',
                'label' => 'Especialidade',
                'rules' => 'required'
            ],
            [
                'field' => 'nif',
                'label' => 'NIF',
                'rules' => 'required|numeric'
            ],
            [
                'field' => 'nib',
                'label' => 'NIB',
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
        $medico = [
            'id' => ($id > 0 ? $id : null), // Ao inserir, se ID é nulo, auto-incrementa na BD.
            'nome' => $this->input->post('nome'),
            'especialidade' => $this->input->post('especialidade'),
            'nif' => $this->input->post('nif'),
            'nib' => $this->input->post('nib') 
        ];
        $morada = [
            'id' => ($id > 0 ? $this->input->post('idmorada') : null),
            'firstLine' => $this->input->post('morada'),
            'secondLine' => $this->input->post('morada2'),
            'zipCode' => $this->input->post('codpostal'),
            'state' => $this->input->post('estado'),
            'city' => $this->input->post('cidade')
        ];

        // Verificar se o médico existe. Se sim, atualizar dados.
        if ($id > 0) {
            // Atualizar dados pelo model.
            $this->medicoModel->update($medico);
            $this->medicoModel->updateMorada($morada);
            // Já atualizámos, o código seguinte apenas serve para inserir.
            return $id;
        }

        // Inserir dados pelo model.
        $moradaId = $this->medicoModel->addMorada($morada);
        $medico['idMorada'] = $moradaId;
        $newId = $this->medicoModel->add($medico);

        // Retornar o ID.
        return $newId;
    }

    protected function getTemplateName() {
        return 'medico';
    }

    protected function temporaryData() {
        return [
            'nome' => 'Vazio ' . date('d/m/yy H:i:s')
        ];
    }
}
