console.log('A carregar...');
window.onload = function () {
  console.log('Carregado.');
  // Como o botão de submição está fora do form, isto tem de ser feito em JS.
  const submitBtn = document.getElementById('insert-btn');
  if (submitBtn) {
    // Listener de click.
    submitBtn.onclick = function () {
      const form = document.getElementById('detailForm');
      if (form) form.submit(); // Submeter.
    }
  }

  // Details de consultas.
  const enfermeiroSearch = document.getElementById('enfermeiro-search');
  const produtoSearch = document.getElementById('produto-search');

  // Se um deles for nulo, não interessa continuar.
  if (!(enfermeiroSearch && produtoSearch)) return;

  // Informação sobre enfermeiros e produtos em JSON está guardada em inputs de tipo hidden.
  const enfermeiroInfo = JSON.parse(document.getElementById('enfermeiros-info').value);
  const produtoInfo = JSON.parse(document.getElementById('produtos-info').value);
  // Inputs de tipo hidden também, mas com as alterações a enviar no form.
  const enfermeiroToSend = document.getElementById('enfermeiros-tosend');
  const produtoToSend = document.getElementById('produtos-tosend');

  // Criar um listener para todos os <tr> clicáveis na tabela de resultados.
  for (const tr of document.getElementsByClassName('selectable-tr')) {
    tr.onclick = handleTrClick;
  }

  // Preencher toSend com informação das variáveis JSON info.
  for (const enfermeiro of JSON.parse(enfermeiroToSend.value.replace(/&quot;/g, '"'))) {
    addToList(enfermeiro.id, 'enfermeiro', enfermeiro.nome);
  }
  for (const produto of JSON.parse(produtoToSend.value.replace(/&quot;/g, '"'))) {
    addToList(produto.id, 'produto', produto.titulo);
  }

  // Inicializar itens da pesquisa nas janelas modais.
  reloadSearch();

  // Tratar da pesquisa.

  // Produtos.
  document.getElementById('produto-search').oninput = function () {
    search('produto', produtoInfo, 'titulo', produtoToSend, this);
  }

  // Enfermeiros.
  document.getElementById('enfermeiro-search').oninput = function () {
    search('enfermeiro', enfermeiroInfo, 'nome', enfermeiroToSend, this);
  }

  function search(type, info, toSearchOn, toSend, context) {
    // Eliminar todos os resultados da tabela primeiro.
    const results = document.getElementById(type + '-results');
    /*
     * Remover usando innerHTML = '' pode ser má prática, porque este
     * é mais aconselhado quando temos texto. Por isso, vamos remover
     * os filhos um por um.
     */
    while (results.lastChild) results.removeChild(results.lastChild);
    /*
     * https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Statements/label
     * 
     * Rótulos:
     * 
     * Se quisermos sair ou continuar quando temos mais que
     * um for/while, e queremos que isto afete uma estrutura
     * externa, podemos usar rótulos. Neste caso, é necessário
     * no caso de encontrarmos alguma entrada nos resultados da
     * pesquisa que não deva aparecer, após uma iteração.
     * 
     * Exemplo:
     * 
     * myFor:
     * for (var i = 0; i <= 10; i++) {
     *   for (var j = 0; j <= 5; j++) {
     *     if (i == 5) break myFor; // Não quebra o for do j, mas sim o for do i.
     *   }
     * }
     */
    infoIterate:
    for (const item of info) {
      if (item[toSearchOn].toLowerCase().startsWith(context.value.toLowerCase())) { // this.value.
        // Verificar se já está na lista toSend.
        for (const toSendItem of JSON.parse(toSend.value.replace(/&quot;/g, '"'))) {
          if (toSendItem.id == item.id) continue infoIterate;
        }
        // Título começa pelo que o user escreveu, por isso, adicionar um novo tr.
        const tr = document.createElement('tr');
        tr.id = type + '-' + item.id;
        tr.classList.add('selectable-tr');
        tr.onclick = handleTrClick;

        // Dinâmicamente adicionar os filhos.
        for (const key in item) {
          const td = document.createElement('td');
          td.classList.add(type + '-' + key);
          td.innerHTML = item[key];
          tr.appendChild(td);
        }

        // Adicionar à tabela.
        results.appendChild(tr);
      }
    }
  }

  function handleTrClick() {
    if (this.id.startsWith('produto')) {
      // Obter ID do produto a partir do ID do elemento.
      const idProduto = this.id.replace('produto-', '');

      const toSend = JSON.parse(produtoToSend.value.replace(/&quot;/g, '"'));

      // Adicionar ao objeto a informação deste produto.
      toSend.push({
        id: idProduto,
        titulo: this.getElementsByClassName('produto-titulo')[0].childNodes[0].nodeValue,
        descricao: this.getElementsByClassName('produto-descricao')[0].childNodes[0].nodeValue
      })
      produtoToSend.value = JSON.stringify(toSend);

      // Atualizar o JSON.
      document.getElementById('produtos-info').value = JSON.stringify(produtoInfo);

      // Adicionar à div.
      addToList(idProduto, 'produto', this.getElementsByClassName('produto-nome')[0].childNodes[0].nodeValue);
    } else if (this.id.startsWith('enfermeiro')) {
      // Obter ID do enfermeiro a partir do ID do elemento.
      const idEnfermeiro = this.id.replace('enfermeiro-', '');
      const nome = this.getElementsByClassName('enfermeiro-nome')[0].childNodes[0].nodeValue;

      const toSend = JSON.parse(enfermeiroToSend.value.replace(/&quot;/g, '"'));

      // Adicionar ao objeto a informação deste enfermeiro.
      toSend.push({ 
        id: idEnfermeiro,
        nome: nome
      });
      enfermeiroToSend.value = JSON.stringify(toSend);

      // Atualizar o JSON.
      document.getElementById('enfermeiros-info').value = JSON.stringify(enfermeiroInfo);

      // Adicionar à div.
      addToList(idEnfermeiro, 'enfermeiro', nome);
    }

    // Remover este elemento.
    this.remove();
  }

  function handleLiClick() {
    const id = this.getAttribute('data-id');
    if (this.parentElement.id === 'produtos-list') {
      const toSend = JSON.parse(produtoToSend.value.replace(/&quot;/g, '"'));
      // Remover do JSON.
      for (const i in toSend) {
        if (toSend[i].id == id) toSend.splice(i, 1); // Remover elemento no índice i.
        break;
      }
      produtoToSend.value = JSON.stringify(toSend);
    } else if (this.parentElement.id === 'enfermeiros-list') {
      const toSend = JSON.parse(enfermeiroToSend.value.replace(/&quot;/g, '"'));
      // Remover do JSON.
      for (const i in toSend) {
        if (toSend[i].id == id) {
          toSend.splice(i, 1); // Remover elemento no índice i.
          break;
        }
      }
      enfermeiroToSend.value = JSON.stringify(toSend);
    }
    // Remover o elemento.
    this.remove();
    // Atualizar a pesquisa.
    reloadSearch();
  }

  function reloadSearch() {
    search('enfermeiro', enfermeiroInfo, 'nome', enfermeiroToSend, { value: '' });
    search('produto', produtoInfo, 'nome', produtoToSend, { value: '' })
  }

  function addToList(id, type, content) {
    const li = document.createElement('li');
    li.classList.add('removable-item');
    // Atributos nossos com prefixo data- são suportados pelo HTML nativamente.
    li.setAttribute('data-id', id);
    li.innerHTML = content;
    li.onclick = handleLiClick;
    document.getElementById(type + 's-list').appendChild(li);
  }
}