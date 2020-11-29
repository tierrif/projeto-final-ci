<? defined('BASEPATH') or exit('No direct script access allowed');

class ContasAdmin extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['serverConfig', 'adapter', 'util', 'form']);
        $this->load->library(['pagination', 'form_validation']);
        if (!$this->authModel->isLoggedIn() || !$this->authModel->hasPermission('manage-accounts')) {
            redirect(base_url('NoAccess'));
        }
    }

    /*
     * Index - Listagem de todas
     * as contas.
     */
    public function index() {
        // Obter a página atual.
        $page = ($this->uri->segment(URI_SEGMENT)) ? $this->uri->segment(URI_SEGMENT) : 0;
        // Configuração da paginação.
        $config['base_url'] = base_url('ContasAdmin/index');
        $config['total_rows'] = $this->authModel->getCount();
        $config['per_page'] = PAGE_NUM_OF_ROWS; // helpers/ServerConfig_helper.php.
        $config['uri_segment'] = URI_SEGMENT; // helpers/ServerConfig_helper.php.
        // Inicializar a paginação.
        $this->pagination->initialize($config);
        $data['contas'] = (new ContaAdminAdapter)->adapt($this->authModel->getAllWithDetails($config['per_page'], $page));
        $data['pagination'] = $this->pagination->create_links();
        $data['conta_form'] = $this->renderer->manualRender('details/conta', [
            'action_uri' => base_url('ContasAdmin/details/-1/insert')
        ]);

        // Carregar template.
        $this->renderer->render('admin/contas', $data, true, true);
    }

    protected function onDelete($id) {
        // Elimina.
        $this->authModel->delete($id);
    }

    /*
     * Details - Detalhes de uma
     * conta específica.
     */
    protected function onDetailsRender($id, $fromForm) {
        // Dados dinâmicos a renderizar no mustache.
        $conta = $this->authModel->getByIdWithDetails($id);
        // Se o pedido for feito pelo form, em POST, não setar $data da base de dados.
        if (!$fromForm) {
          $data = (new ContaDetailsAdapter)->adapt($conta);
          $data['permissions_value'] = implode(',', unserialize($data['permissions_value']));
        } else $data = ['username_value' => set_value('username'), 'permissions_value' => set_value('permissions')];
          
        $data['bar'] = $this->renderer->manualRender('includes/bar', ['base_controller' => base_url(get_class($this))]);

        return $data;
    }

    protected function formElements() {
        return [
            [
                'field' => 'username',
                'label' => 'username',
                'rules' => 'required'
            ],
            [
                'field' => 'password',
                'label' => 'password',
                'rules' => 'required'
            ]
        ];
    }

    protected function handleDatabaseCalls($id) {
        // Arrays associativos que funcionam tanto para UPDATE como INSERT.
        $conta = [
            'id' => ($id > 0 ? $id : null), // Ao inserir, se ID é nulo, auto-incrementa na BD.
            'username' => $this->input->post('username'),
            'password' => hash('sha256', $this->input->post('password')),
            'permissions' => serialize(explode(',', $this->input->post('permissions')))
        ];

        // Verificar se a conta existe. Se sim, atualizar dados.
        if ($id > 0) {
            // Atualizar dados pelo model.
            $this->authModel->update($conta);
            // Já atualizámos, o código seguinte apenas serve para inserir.
            return $id;
        }

        $newId = $this->authModel->add($conta);

        // Retornar o ID.
        return $newId;
    }

    protected function getTemplateName() {
        return 'conta';
    }

    protected function temporaryData() {
        return [
            'username' => 'vazio' . date('d/m/yy H:i:s')
        ];
    }
}
