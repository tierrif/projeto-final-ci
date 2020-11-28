<? defined('BASEPATH') or exit('No direct script access allowed');

class ConsultasAdmin extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->helper(['login', 'serverConfig', 'adapter', 'util', 'form']);
        $this->load->library(['pagination', 'form_validation']);
        $this->load->model(['consultaModel', 
            'enfermagemModel', 
            'produtoReceitaModel', 
            'produtoModel', 
            'receitaModel', 
            'enfermeiroModel'
        ]);
        if (!isLoggedIn() || !hasPermission('edit-consultas', $this->session)) {
            redirect(base_url('noaccess'));
        }
    }

    /*
     * Index - Listagem de todas as
     * consultas.
     */
    public function index() {
        // Obter a página atual.
        $page = ($this->uri->segment(URI_SEGMENT)) ? $this->uri->segment(URI_SEGMENT) : 0;
        // Configuração da paginação.
        $config['base_url'] = base_url('ConsultasAdmin/index');
        $config['total_rows'] = $this->consultaModel->getCount();
        $config['per_page'] = PAGE_NUM_OF_ROWS; // helpers/ServerConfig_helper.php.
        $config['uri_segment'] = URI_SEGMENT; // helpers/ServerConfig_helper.php.
        // Inicializar a paginação.
        $this->pagination->initialize($config);
        $data['consultas'] = (new ConsultaAdminAdapter)->adapt($this->consultaModel->getAllWithReplacedKeys($config['per_page'], $page, 'ConsultasAdmin'));
        $data['pagination'] = $this->pagination->create_links();
        $data['consulta_form'] = $this->renderer->manualRender('details/consulta', [
            'morada_form_include' => $this->renderer->manualRender('includes/morada_form', []),
            'action_uri' => base_url('ConsultasAdmin/details/-1/insert')
        ]);

        // Carregar template.
        $this->renderer->render('admin/consultas', $data, true, true);
    }

    protected function onDelete($id) {
        // Elimina.
        $this->consultaModel->deleteAlongReceita($id);
    }

    /*
     * Details - Detalhes de uma
     * consulta específica.
     */
    protected function onDetailsRender($id, $fromForm) {
        // Dados dinâmicos a renderizar no mustache.
        $consulta = $this->consultaModel->getByIdWithReplacedKeys($id);
        // Obter todos os produtos e enfermeiros.
        $enfermeiros = $this->enfermeiroModel->getAll();
        $produtos = $this->produtoModel->getAll();
        // Se o pedido for feito pelo form, em POST, não setar $data da base de dados.
        if (!$fromForm) {
            $data = (new ConsultaDetailsAdapter)->adapt($consulta);
            $data['receita_form_include'] = $this->renderer->manualRender('includes/receita_form',
            (new ReceitaDetailsAdapter)->adapt($consulta['receita']));
            $data['modals_include'] = $this->renderer->manualRender('includes/add_prod_enf_modal', []);
            $data['produtos_json'] = json_encode($produtos);
            $data['enfermeiros_json'] = json_encode($enfermeiros);
        } else {
            $data = [
                'data_value' => set_value('data'),
                'estado_value' => set_value('estado'),
                'medico_value' => set_value('medico'),
                'utente_value' => set_value('utente'),
                'receita_form_include' => $this->renderer->manualRender('includes/morada_form', [
                    'id_receita' => (set_value('idreceita') ? set_value('idreceita') : $consulta['receita']['id']),
                    'cuidado_value' => set_value('cuidado'),
                    'receita_value' => set_value('receita')
                ]),
                'modals_include' => $this->renderer->manualRender('add_prod_enf_modal', []),
                'produtos_json' => json_encode($produtos),
                'enfermeiros_json' => json_encode($enfermeiros)
            ];
        }

        return $data;
    }

    protected function formElements() {
        // Inicializar regras por defeito.
        $toReturn = [
            [
                'field' => 'data',
                'label' => 'data',
                'rules' => 'required'
            ],
            [
                'field' => 'estado',
                'label' => 'estado',
                'rules' => 'required'
            ],
            [
                'field' => 'medico',
                'label' => 'médico',
                'rules' => 'required'
            ],
            [
                'field' => 'utente',
                'label' => 'utente',
                'rules' => 'required'
            ]
        ];

        // Verificar se foi passada receita ou cuidado.
        if ($this->input->post('receita') || $this->input->post('cuidado')) {
            // Se houver receita, estas são as regras.
            $toReturn[] = [
                [
                    'field' => 'receita',
                    'label' => 'receita',
                    'rules' => 'required'
                ],
                [
                    'field' => 'cuidado',
                    'label' => 'cuidado',
                    'rules' => 'required'
                ]
            ];
        }
        return $toReturn;
    }

    protected function handleDatabaseCalls($id) {
        // Arrays associativos que funcionam tanto para UPDATE como INSERT.
        $consulta = [
            'id' => ($id > 0 ? $id : null), // Ao inserir, se ID é nulo, auto-incrementa na BD.
            'data' => $this->input->post('data'),
            'estado' => $this->input->post('estado'),
            'idMedico' => $this->input->post('medico'),
            'idUtente' => $this->input->post('utente')
        ];
        $receita = [
            'id' => ($id > 0 ? $this->input->post('idreceita') : null),
            'cuidado' => $this->input->post('cuidado'),
            'receita' => $this->input->post('receita')
        ];
        $enfermagens = [];
        // Se passado, enfermeiros está em JSON.
        if ($this->input->post('enfermeiros_tosend')) {
            // Passar de JSON para array associativo.
            $decoded = json_decode($this->input->post('enfermeiros'), true);
            // O JSON passado é um array. Iterá-lo.
            foreach ($decoded as $enfermeiro) {
                $enfermagens[] = [
                    'idEnfermeiro' => $enfermeiro['id'],
                    'idConsulta' => $id
                ];
            }
        }
        $produtoreceitas = [];
        $produtos = [];
        // Se passado, produtos está em JSON.
        if ($this->input->post('produtos_tosend')) {
            // Passar de JSON para array associativo.
            $decoded = json_decode($this->input->post('enfermeiros'), true);
            // O JSON passado é um array. Iterá-lo.
            foreach ($decoded as $produto) {
                $produtoreceitas[] = [
                    'idProduto' => arrayValue($produto, 'id'),
                    'idReceita' => $this->input->post('idreceita')
                ];
                $produtos[] = [
                    'id' => arrayValue($produto, 'id'),
                    'titulo' => $produto['titulo'],
                    'descricao' => arrayValue($produto, 'descricao')
                ];
            }
        }

        // Verificar se a consulta existe. Se sim, atualizar dados.
        if ($id > 0) {
            // Atualizar dados pelo model.
            $this->consultaModel->update($consulta);
            $this->consultaModel->updateReceita($receita);
            // Eliminar todas as enfermagens que existem para atualizar.
            $this->consultaModel->deleteEnfermagensByConsulta($id);
            // Iterar todas as novas enfermagens.
            foreach ($enfermagens as $enfermagem) {
                // Adicionar a nova enfermagem.
                $this->enfermagemModel->add($enfermagem);
            }
            // Eliminar todos os produtosreceitas que existem para atualizar.
            $this->consultaModel->deleteProdutosReceitasByReceita($this->input->post('idreceita'));
            // Iterar todos os novos produtosreceitas.
            foreach ($produtoreceitas as $pr) {
                // Adicionar o novo produtoreceita.
                $this->produtoReceitaModel->add($pr);
            }
            // Iterar todos os produtos.
            foreach ($produtos as $produto) {
                if (arrayValue($produto, 'id')) {
                    // Atualizar se já existe.
                    $this->produtoModel->update($produto);
                    continue;
                }
                // Não existe, por isso, inserir.
                $this->produtoModel->add($produto);
            }
            // Já atualizámos, o código seguinte apenas serve para inserir.
            return $id;
        }

        // Inserir dados pelo model.
        $receitaId = $this->receitaModel->add($receita);
        $enfermeiro['idReceita'] = $receitaId;
        $newId = $this->consultaModel->add($enfermeiro);
        // Iterar todas as novas enfermagens.
        foreach ($enfermagens as $enfermagem) {
            // Adicionar a nova enfermagem.
            $this->enfermagemModel->add($enfermagem);
        }
        // Iterar todos os novos produtosreceitas.
        foreach ($produtoreceitas as $pr) {
            // Adicionar o novo produtoreceita.
            $this->produtoReceitaModel->add($pr);
        }
        // Iterar todos os produtos.
        foreach ($produtos as $produto) {
            if (arrayValue($produto, 'id')) {
                // Atualizar se já existe.
                $this->produtoModel->update($produto);
                continue;
            }
            // Não existe, por isso, inserir.
            $this->produtoModel->add($produto);
        }

        // Retornar o ID.
        return $newId;
    }

    protected function getTemplateName() {
        return 'consulta';
    }

    protected function temporaryData() {
        return [
            'nome' => 'Vazio ' . date('d/m/yy H:i:s')
        ];
    }
}
