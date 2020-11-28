<? defined('BASEPATH') or exit('No direct script access allowed');

class ConsultaModel extends MY_Model {
    public function getConsultasOfTheDay($limit, $start) {
        // Inicializar array associativo a retornar.
        $toReturn = [];
        // Filtrar apenas para resultados do dia.
        $this->db->where('data', date('Y-m-d'));
        // Ordenar consultas pelo estado, para que apareçam as por fazer primeiro.
        $this->db->order_by('estado', 'ASC');
        // Obter todas as consultas.
        $all = $this->getAll($limit, $start);
        // Substituir todas as chaves estrangeiras.
        foreach ($all as $one) {
            // Utente.
            $one['utente'] = $this->db->get_where(parent::UTENTE_TABLE, ['id' => $one['idUtente']])->row_array();
            // Médico.
            $one['medico'] = $this->db->get_where(parent::MEDICO_TABLE, ['id' => $one['idMedico']])->row_array();
            // Receita.
            $one['receita'] = $this->db->get_where(parent::RECEITA_TABLE, ['id' => $one['idReceita']])->row_array();
            // Query à tabela N-N enfermagem, para obter uma lista de enfermeiros.
            $this->db->where('idConsulta', $one['id']);
            // Query à BD.
            $enfermagens = $this->db->get(parent::ENFERMAGEM_TABLE)->result_array();
            // Inicializar o array de enfermeiros.
            $one['enfermeiros'] = [];
            // Iterar as enfermagens para obter todos os enfermeiros.
            foreach ($enfermagens as $enfermagem) {
                // Enfermeiro.
                $enfermeiro = $this->db->get_where(parent::ENFERMEIRO_TABLE, ['id' => $enfermagem['idEnfermeiro']])->row_array();
                // Adicionar ao array de enfermeiros da consulta.
                $one['enfermeiros'][] = $enfermeiro;
            }

            // Remover todas as chaves estrangeiras da consulta.
            unset($one['idUtente']);
            unset($one['idMedico']);
            unset($one['idReceita']);

            // Traduzir valor booleano para português.
            $one['estado'] = arrayValue($one, 'estado') ? '<span class="green">terminada</span>' 
                : '<span class="red">pendente</span>';
            // Traduzir valor booleano para âncora ou texto simples.
            $one['receita'] = arrayValue($one, 'receita') ? '<a class="table-link" href="' . base_url('Receitas/details/' . $one['receita']['id']) . '">sim</a>'
                : '<span class="gray-default-cursor">não</span>';

            // Adicionar ao array de retorno esta consulta.
            $toReturn[] = $one;
        }

        // Retornar.
        return $toReturn;
    }

    public function getTable() {
        return 'consulta';
    }
}