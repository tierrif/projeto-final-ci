<? defined('BASEPATH') or exit('No direct script access allowed');

class UtenteModel extends MY_Model {
    public function getTable() {
        return 'utente';
    }

    public function getConsultas($utenteId) {
        // Obter todas as consultas deste utente.
        $query = $this->db->get_where(parent::CONSULTA_TABLE, ['idUtente' => $utenteId]);
        // Retornar o resultado, em formato de array para que seja iterável.
        return $query->result_array();
    }

    public function getAllWithMoradaAndConsultas($limit, $start) {
        // Inicializar array a retornar.
        $toReturn = [];
        // Obter todos os utentes já com a morada.
        $all = $this->getAllWithMorada($limit, $start);
        // Iterar todos.
        foreach ($all as $one) {
            // Adicionar um array de consultas.
            $one['consultas'] = $this->getConsultas($one['id']);
            // Adicionar o estado das consultas.
            $one['consultas_inacabadas'] = $this->getQuantidadeConsultasInacabadas($one['consultas']);
            // Classe para tornar a âncora desativada se não houverem consultas.
            $one['link_class'] = $one['consultas_inacabadas'] ? TABLE_LINK_CLASS_NAME : DISABLED_LINK_CLASS_NAME;
            // Se consultas_inacabadas for 0, mensagem por defeito:
            if (!$one['consultas_inacabadas']) $one['consultas_inacabadas'] = DEFAULT_CONSULTAS_INACABADAS;
            // Adicionar URI para mostrar as consultas pendentes.
            $one['consultas_uri'] = base_url('consultasAdmin/perUtente/' . $one['id'] . '/finishedOnly');
            // Adicionar URI para os detalhes do utente.
            $one['detalhes_uri'] = base_url('utentesAdmin/details/' . $one['id']);
            // Adicionar ao array a retornar este utente atualizado.
            $toReturn[] = $one;
        }

        // Retornar.
        return $toReturn;
    }

    private function getQuantidadeConsultasInacabadas($consultas) {
        // Inicializar a quantidade de consultas.
        $quantity = 0; // 0 poderá ser usado num if para usar uma string se não houverem consultas.
        // Iterar as consultas.
        foreach ($consultas as $consulta) {
            // Se o estado for verdadeiro (ou seja, terminado), continuar.
            if (arrayValue($consulta, 'estado')) continue;
            // Incrementar.
            $quantity += 1;
        }

        // Retornar.
        return $quantity;
    }
}