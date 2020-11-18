<? defined('BASEPATH') or exit('No direct script access allowed');

class UtenteModel extends MY_Model {
    private $moradaTable = 'morada';
    private $consultaTable = 'consulta';

    public function getTable() {
        return 'utente';
    }

    public function getMoradaById($id) {
        // Obter uma morada através de $id.
        $query = $this->db->get_where($this->moradaTable, ['id' => $id]);
        // Retornar o resultado, em formato de array para que seja iterável.
        return $query->row_array();
    }

    public function getConsultas($utenteId) {
        // Obter todas as consultas deste utente.
        $query = $this->db->get_where($this->consultaTable, ['idUtente' => $utenteId]);
        // Retornar o resultado, em formato de array para que seja iterável.
        return $query->result_array();
    }

    public function getAllWithMorada($limit, $start) {
        // Inicializar array a retornar.
        $toReturn = [];
        // Obter todos os utentes.
        $all = $this->getAll($limit, $start);
        // Iterar todos os utentes.
        foreach ($all as $one) {
            // Adicionar a chave 'morada' para que seja substituído o ID pelo objeto.
            $one['morada'] = $this->getMoradaById($one['idMorada']);
            // Remover o ID, que deixa de ser necessário.
            unset($one['idMorada']);
            // Adicionar ao array a retornar este utente atualizado.
            $toReturn[] = $one;
        }

        // Retornar.
        return $toReturn;
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
            // Se consultas_inacabadas for 0, mensagem por defeito:
            if (!$one['consultas_inacabadas']) $one['consultas_inacabadas'] = DEFAULT_CONSULTAS_INACABADAS;
            // Adicionar URI para mostrar as consultas pendentes.
            $one['consultas_uri'] = base_url('consultasAdmin/perUtente/' . $one['id'] . '/finishedOnly');
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