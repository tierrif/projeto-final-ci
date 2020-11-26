<? defined('BASEPATH') or exit('No direct script access allowed');

abstract class Adapter {
    /*
     * Retornar um array associativo que contém
     * como chave o novo nome, e o valor com o
     * nome original. Por exemplo:
     * Array original:
     * [
     *   'nome' => 'Tierri Ferreira',
     *   'morada' => [
     *      'linha1' => 'Rua abcde',
     *      'cidade' => 'Funchal'
     *   ]
     * ]
     * A retornar:
     * [
     *   'nome', // Pode ser equivalente a 'nome' => 'nome'
     *   'cidade' => 'morada/cidade' // cidade, conteúdo do array da morada.
     * ]
     */
    public abstract function toAdapt();

    /*
     * Adaptar. Retornará em array no formato
     * do adaptador respetivo, delimitado
     * pelo método abstrato toAdapt().
     */
    public function adapt($originalContent) {
        $toReturn = [];
        // Primeiro iterar o array original.
        foreach ($originalContent as $item) {
            // Inicializar array adaptado.
            $adaptedArray = [];
            // Iterar.
            foreach ($this->toAdapt() as $key => $value) {
                if (is_numeric($key)) $key = $value; // No caso de ser elemento simples.
                // Transformar a string, fazendo com que possamos iterar o que está ao lado de '/'.
                $split = explode('/', $value);
                // A primeira posição. Será atualizada em cada nível.
                $pos = arrayValue($item, $split[0]);
                // Variável de controlo para prevenir repetição do primeiro elemento.
                $i = 0;
                foreach ($split as $element) {
                    // Se não existirem barras, isto não é necessário.
                    if (count($split) === 1) break;
                    // Prevenir repetição do primeiro elemento.
                    if ($i++ === 0) continue;
                    // Atualizar o array com novo nível.
                    $pos = arrayValue($pos, $element);
                    // Se for nulo, adicionar valor por defeito.
                    if ($pos === null) $pos = DEFAULT_VALUE;
                }
                $adaptedArray[$key] = $pos;
            }
            $toReturn[] = $adaptedArray;
        }
        // Retornar o array adaptado.
        return $toReturn;
    }
}

/*
 * Adapter para apenas um item. Adapter é
 * por defeito para listas.
 */
abstract class SingleItemAdapter extends Adapter {
    public function adapt($originalContent) {
        // Inicializar array adaptado.
        $adaptedArray = [];
        // Iterar.
        foreach ($this->toAdapt() as $key => $value) {
            if (is_numeric($key)) $key = $value; // No caso de ser elemento simples.
            // Transformar a string, fazendo com que possamos iterar o que está ao lado de '/'.
            $split = explode('/', $value);
            // A primeira posição. Será atualizada em cada nível.
            $pos = arrayValue($originalContent, $split[0]);
            // Variável de controlo para prevenir repetição do primeiro elemento.
            $i = 0;
            foreach ($split as $element) {
                // Se não existirem barras, isto não é necessário.
                if (count($split) === 1) break;
                // Prevenir repetição do primeiro elemento.
                if ($i++ === 0) continue;
                // Atualizar o array com novo nível.
                $pos = arrayValue($pos, $element);
                // Se for nulo, adicionar valor por defeito.
                if ($pos === null) $pos = DEFAULT_VALUE;
            }
            $adaptedArray[$key] = $pos;
        }
        return $adaptedArray;
    }
}

class PessoaSimplesAdapter extends Adapter {
    public function toAdapt() {
        return [
            'nome',
            'cidade' => 'morada/city'
        ];
    }
}

class UtenteAdminAdapter extends Adapter {
    public function toAdapt() {
        return [
            'nome',
            'numero_utente' => 'nUtente',
            'cidade' => 'morada/city',
            'consultas_inacabadas',
            'consultas_uri',
            'detalhes_uri',
            'link_class'
        ];
    }
}

class UtenteDetailsAdapter extends SingleItemAdapter {
    public function toAdapt() {
        return [
            'nome_value' => 'nome',
            'num_utente_value' => 'nUtente'
        ];
    }
}

class MoradaDetailsAdapter extends SingleItemAdapter {
    public function toAdapt() {
        return [
            'morada_linha_1_value' => 'firstLine',
            'morada_linha_2_value' => 'secondLine',
            'cidade_value' => 'city',
            'estado_value' => 'state',
            'codigo_postal_value' => 'zipCode',
            'id_morada' => 'id'
        ];
    }
}

class EnfermeiroAdminAdapter extends Adapter {
    public function toAdapt() {
        return [
            'nome',
            'especialidade',
            'cidade' => 'morada/city',
            'detalhes_uri',
            'link_class'
        ];
    }
}

class EnfermeiroDetailsAdapter extends SingleItemAdapter {
    public function toAdapt() {
        return [
            'nome_value' => 'nome',
            'nif_value' => 'nif',
            'nib_value' => 'nib',
            'especialidade_value' => 'especialidade'
        ];
    }
}

class MedicoAdminAdapter extends Adapter {
    public function toAdapt() {
        return [
            'nome',
            'especialidade',
            'cidade' => 'morada/city',
            'detalhes_uri',
            'link_class'
        ];
    }
}

class MedicoDetailsAdapter extends SingleItemAdapter {
    public function toAdapt() {
        return [
            'nome_value' => 'nome',
            'nif_value' => 'nif',
            'nib_value' => 'nib',
            'especialidade_value' => 'especialidade'
        ];
    }
}
