<?php

/**
 * Exibe uma lista detalhada dos servidores
 * 
 * esta classe foi criada devido a sua grande (re)usabilidade
 * 
 * @author Alat
 */
class ListaServidores {
    # Nome da lista que aparece no título

    private $nomeLista = null;

    /*
     * Parâmetros de Pesquisa
     */
    private $matNomeId = null;
    private $cargo = null;
    private $area = null;
    private $tipoCargo = null;
    private $cargoComissao = null;
    private $perfil = null;
    private $concurso = null;
    private $situacao = null;
    private $situacaoSinal = "=";
    private $lotacao = null;

    /*
     * da listagem
     */
    private $permiteEditar = true;
    private $ordenacao = "2 asc";               # ordenação da listagem. Padrão 3 por nome
    private $ordenacaoCombo = array();          # Array da combo de ordanação

    /*
     *  Outros
     */
    private $totReg = 0;        # Total de registros encontrados
    private $detalhado = true;  # Exibe detalhes 

    /*
     *  Parâmetros da paginação da listagem
     */
    private $paginacao = false;   # Flag que indica se terá ou não paginação na lista
    private $paginacaoItens = 15;  # Quantidade de registros por página. 
    private $paginacaoInicial = 0;  # A paginação inicial
    private $pagina = 1;   # Página atual
    private $quantidadeMaxLinks = 10;           # Quantidade Máximo de links de paginação a ser exibido na página
    private $texto = null;                      # texto a ser exibido no rodapé indicando quantas páginas e a página atual
    private $itemFinal = null;
    private $itemInicial = null;

    /*
     * Parâmetros do relatório
     */
    private $select = null;     // Guarda o select para ser recuperado pela rotina de relatório
    private $selectPaginacao = null;  // Guarda o texto acrescido ao select quando se tem paginação
    private $titulo = null;     // guarda o título do relatório que é montado a partir da pesquisa
    private $subTitulo = null;  // guarda o subTítulo do relatório que é montado a partir da pesquisa

    ###########################################################

    /**
     * método construtor
     * inicia um Formulário
     * 
     * @param  $name    = nome da classe e do id para estilo
     */
    public function __construct($nome) {
        $this->nomeLista = $nome;

        $this->ordenacaoCombo = array(
            array("1 asc", "por Id Funcional asc"),
            array("1 desc", "por Id Funcional desc"),
            array("2 asc", "por Nome asc"),
            array("2 desc", "por Nome desc"),
            array("tbtipocargo.sigla asc,tbcargo.nome asc", "por Cargo asc"),
            array("tbtipocargo.sigla desc,tbcargo.nome desc", "por Cargo desc"),
            array("UADM asc, DIR asc, GER asc", "por Lotação asc"),
            array("UADM desc, DIR desc, GER desc", "por Lotação desc"),
            array("tbperfil.nome asc", "por Perfil asc"),
            array("tbperfil.nome desc", "por Perfil desc"),
            array("6 asc", "por Admissão asc"),
            array("6 desc", "por Admissão desc"),
            array("tbsituacao.situacao asc", "por Situação asc"),
            array("tbsituacao.situacao desc", "por Situação desc")
        );
    }

    ###########################################################

    /**
     * Métodos get e set construídos de forma automática pelo 
     * metodo mágico __call.
     * Esse método cria um set e um get para todas as propriedades da classe.
     * Um método existente tem prioridade sobre os métodos criados pelo __call.
     * 
     * O formato dos métodos devem ser:
     * 	set_propriedade
     * 	get_propriedade
     * 
     * @param 	$metodo		O nome do metodo
     * @param 	$parametros	Os parâmetros inseridos  
     */
    public function __call($metodo, $parametros) {
        ## Se for set, atribui um valor para a propriedade
        if (substr($metodo, 0, 3) == 'set') {
            $var = substr($metodo, 4);
            $this->$var = $parametros[0];
        }

        # Se for Get, retorna o valor da propriedade
        if (substr($metodo, 0, 3) == 'get') {
            $var = substr($metodo, 4);
            return $this->$var;
        }
    }

    ###########################################################

    /**
     * Método prepara
     * 
     * Exibe a lista
     *
     */
    private function prepara() {
        # Pega o time inicial
        #$this->time_start = microtime(true);
        # Inicia variáveis
        $tipo = null;

        # Pega o tipo do concurso quando se solicita servidores de um concurso específico
        if (!is_null($this->concurso)) {
            # Verifica se o concurso é de Adm & Tec ou se é de Professor
            $concurso = new Concurso();
            $dados = $concurso->get_dados($this->concurso);
            $tipo = $dados['tipo'];
        }

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $select = 'SELECT tbservidor.idServidor,
                          tbpessoa.nome,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.idServidor,
                          tbservidor.dtAdmissao,';

        if (($this->situacao <> 1) OR (($this->situacao == 1) AND ($this->situacaoSinal == "<>"))) {
            $select .= 'tbservidor.dtDemissao,';
        }

        $select .= '      tbservidor.idServidor,
                          tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbpessoa ON (tbservidor.idPessoa = tbpessoa.idPessoa)
                                     LEFT JOIN tbhistlot ON (tbservidor.idServidor = tbhistlot.idServidor)
                                     LEFT JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                                     LEFT JOIN tbsituacao ON (tbservidor.situacao = tbsituacao.idsituacao)
                                     LEFT JOIN tbperfil ON (tbservidor.idPerfil = tbperfil.idPerfil)
                                     LEFT JOIN tbcargo ON (tbservidor.idCargo = tbcargo.idCargo)
                                     LEFT JOIN tbtipocargo ON (tbcargo.idTipoCargo = tbtipocargo.idTipoCargo)';
        # Área
        if (!is_null($this->area)) {
            $select .= ' LEFT JOIN tbarea ON (tbcargo.idArea = tbarea.idArea)';
        }

        # CArgo em comissão
        if (!is_null($this->cargoComissao)) {
            $select .= ' LEFT JOIN tbcomissao ON (tbservidor.idServidor = tbcomissao.idServidor)
                         LEFT JOIN tbtipocomissao ON (tbcomissao.idTipoComissao = tbtipocomissao.idTipoComissao)';
        }

        # Concurso tipo 2 (professor)
        if ($tipo == 2) {
            $select .= ' JOIN tbvagahistorico ON (tbvagahistorico.idServidor = tbservidor.idServidor)';
        }

        $select .= ' WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)';

        # Matrícula, nome ou id
        if (!is_null($this->matNomeId)) {
            if (is_numeric($this->matNomeId)) {
                $select .= ' AND ((';
            } else {
                $select .= ' AND (';
            }

            $select .= 'tbpessoa.nome LIKE "%' . $this->matNomeId . '%")';

            if (is_numeric($this->matNomeId)) {
                $select .= ' OR (tbservidor.matricula LIKE "%' . $this->matNomeId . '%")
		             OR (tbservidor.idfuncional LIKE "%' . $this->matNomeId . '%"))';
            }
            $this->subTitulo .= "pesquisa: " . $this->matNomeId . "<br/>";
        }

        # situação
        if (!is_null($this->situacao)) {
            $select .= ' AND (tbsituacao.idsituacao ' . $this->situacaoSinal . ' "' . $this->situacao . '")';

            if (($this->situacaoSinal == "<>") AND ($this->situacao == 1)) {
                $this->titulo .= "Inativos";
            } else {

                if ($this->situacao == 6) {
                    $this->titulo .= " em " . $servidor->get_nomeSituacao($this->situacao);
                } else {
                    $this->titulo .= $servidor->get_nomeSituacao($this->situacao) . "s";
                }
            }
        }

        # perfil
        if (!is_null($this->perfil)) {
            $select .= ' AND (tbperfil.idperfil = "' . $this->perfil . '")';
            $this->subTitulo .= "Perfil: " . $servidor->get_nomePerfil($this->perfil) . "<br/>";
        }

        # tipoCargo
        if (!is_null($this->tipoCargo)) {
            $select .= ' AND (tbcargo.idTipoCargo = ' . $this->tipoCargo . ')';
            $this->subTitulo .= "Cargo: " . $servidor->get_nomeTipoCargo($this->tipoCargo) . "<br/>";
        }

        # area
        if (!is_null($this->area)) {
            $select .= ' AND (tbarea.idArea = ' . $this->area . ')';
            $this->subTitulo .= "Area: " . $servidor->get_area($this->area) . "<br/>";
        }

        # cargo
        if (!is_null($this->cargo)) {
            if (is_numeric($this->cargo)) {
                $select .= ' AND (tbcargo.idcargo = "' . $this->cargo . '")';
                $this->subTitulo .= "Cargo: " . $servidor->get_nomeCompletoCargo($this->cargo) . "<br/>";
            } else { # senão é nivel do cargo
                if ($this->cargo == "Professor") {
                    $select .= ' AND (tbcargo.idcargo = 128 OR  tbcargo.idcargo = 129)';
                    $this->subTitulo .= "Cargo: Professor<br/>";
                } else {
                    $select .= ' AND (tbtipocargo.cargo = "' . $this->cargo . '")';
                    $this->subTitulo .= "Cargo: " . $this->cargo . "<br/>";
                }
            }
        }

        # cargo em comissão
        if (!is_null($this->cargoComissao)) {
            $select .= ' AND tbcomissao.dtExo is null AND tbtipocomissao.idTipoComissao = "' . $this->cargoComissao . '"';
            $this->subTitulo .= "Cargo em comissão: " . $servidor->get_nomeCargoComissao($this->cargoComissao) . "<br/>";
        }

        # concurso
        if (!is_null($this->concurso)) {
            if ($tipo == 1) {
                $select .= ' AND (tbservidor.idConcurso = ' . $this->concurso . ')';
            } else {
                $select .= ' AND (tbvagahistorico.idConcurso = ' . $this->concurso . ')';
            }
            $this->subTitulo .= "Concurso: " . $concurso->get_nomeConcurso($this->concurso) . "<br/>";
        }

        # lotacao
        if (!is_null($this->lotacao)) {
            # Verifica se o que veio é numérico
            if (is_numeric($this->lotacao)) {
                $select .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
                $this->subTitulo .= "Lotação: " . $servidor->get_nomeLotacao($this->lotacao) . " - " . $servidor->get_nomeCompletoLotacao($this->lotacao) . "<br/>";
            } else { # senão é uma diretoria genérica
                $select .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
                $this->subTitulo .= "Lotação: " . $this->lotacao . "<br/>";
            }
        }

        # ordenação
        $select .= " ORDER BY $this->ordenacao";

        # Garante que não importando a ordenação principal a listagem sempre ordenara em segundo plano por nome
        if (($this->ordenacao <> "2 asc") AND ($this->ordenacao <> "2 desc")) {
            $select .= ", 2 asc";
        }

        # Garante que não importando a ordenação principal a listagem sempre ordenara em segundo plano por admissão
        if (($this->ordenacao <> "6 asc") AND ($this->ordenacao <> "6 desc")) {
            $select .= ", 6 asc";
        }

        foreach ($this->ordenacaoCombo as $value) {
            if ($value[0] == $this->ordenacao) {
                $this->subTitulo .= "Ordenado " . $value[1] . "<br/>";
            }
        }

        # Pega a quantidade de itens da lista
        $conteudo = $servidor->select($select, true);
        $totalRegistros = count($conteudo);

        # Verifica a necessidade de paginação pelo número de registro
        if ($this->paginacaoItens >= $totalRegistros) {
            $this->paginacao = false;
        }

        # Verifica se página Inicial que veio por session deverá ser atualizada para 0
        if ($this->paginacaoInicial > $totalRegistros) {
            $this->paginacaoInicial = 0;
        }

        # Calculos da paginaçao
        $this->texto = null;
        if ($this->paginacao) {
            # Calcula o total de páginas
            $totalPaginas = ceil($totalRegistros / $this->paginacaoItens);

            # Calcula o número da página
            $this->pagina = ceil($this->paginacaoInicial / $this->paginacaoItens) + 1;

            # Calcula o item inicial e final da página
            $this->itemFinal = $this->pagina * $this->paginacaoItens;
            $this->itemInicial = $this->itemFinal - $this->paginacaoItens + 1;

            if ($this->itemFinal > $totalRegistros) {
                $this->itemFinal = $totalRegistros;
            }

            # Texto do fieldset
            $this->texto = 'Página: ' . $this->pagina . ' de ' . $totalPaginas;

            # Acrescenta a sql para paginacao
            $this->selectPaginacao = ' LIMIT ' . $this->paginacaoInicial . ',' . $this->paginacaoItens;

            # Botôes de Navegação das páginas 
            $proximo = $this->paginacaoInicial + $this->paginacaoItens;
            $anterior = $this->paginacaoInicial - $this->paginacaoItens;
        }

        # Botões de paginação
        if ($this->paginacao) {
            # Começa os botões de navegação
            $div = new Div("paginacao");
            $div->abre();
            echo'<ul class="pagination text-center" role="navigation" aria-label="Pagination">';

            # Botão Página Anterior
            if ($this->pagina == 1) {
                echo '<li class="pagination-previous disabled"><span class="show-for-sr">page</span></li>';
            } else {
                echo '<li class="pagination-previous"><a href="?paginacao=' . $anterior . '" aria-label="Página anterior"></a></li>';
            }

            # Links para a página
            for ($pag = 1; $pag <= $totalPaginas; $pag++) {
                if ($pag == $this->pagina) {
                    echo '<li class="current"><span class="show-for-sr">Página Atual</span> ' . $pag . '</li>';
                } else {
                    $link = $this->paginacaoItens * ($pag - 1);

                    if ($totalPaginas > $this->quantidadeMaxLinks) {
                        switch ($pag) {
                            case 1:
                            case 2:
                                echo '<li><a href="?paginacao=' . $link . '" aria-label="Pagina ' . $pag . '">' . $pag . '</a></li>';
                                break;
                            case 3:
                                if ($this->pagina == 2) {
                                    echo '<li><a href="?paginacao=' . $link . '" aria-label="Pagina ' . $pag . '">' . $pag . '</a></li>';
                                } else {
                                    echo '<li>...<li>';
                                }
                                break;
                            case $this->pagina - 1:
                            case $this->pagina + 1:
                                echo '<li><a href="?paginacao=' . $link . '" aria-label="Pagina ' . $pag . '">' . $pag . '</a><li>';
                                break;
                            case $totalPaginas - 2:
                                if ($this->pagina == $this->pagina - 4) {
                                    echo '<li><a href="?paginacao=' . $link . '" aria-label="Pagina ' . $pag . '">' . $pag . '</a></li>';
                                } else {
                                    echo '<li>...<li>';
                                }
                                break;
                            case $totalPaginas - 1:
                            case $totalPaginas:
                                echo '<li><a href="?paginacao=' . $link . '" aria-label="Pagina ' . $pag . '">' . $pag . '</a></li>';
                                break;
                        }
                    } else {
                        echo '<li><a href="?paginacao=' . $link . '" aria-label="Pagina ' . $pag . '">' . $pag . '</a></li>';
                    }
                }
            }

            # Botão Próxima Página
            if ($this->pagina < $totalPaginas) {
                echo '<li class="pagination-next"><a href="?paginacao=' . $proximo . '" aria-label="Próxima página"><span class="show-for-sr">page</span></a></li>';
            } else {
                echo '<li class="pagination-next disabled"><span class="show-for-sr">page</span></li>';
            }
            echo '</ul>';
            $div->fecha();
        }

        # Passa para as variaveis da classe
        $this->select = $select;
        $this->totReg = $totalRegistros;
    }

    ###########################################################

    /**
     * Método showTabela
     * 
     * Exibe a Tabela
     *
     */
    public function showTabela() {

        # Pega o time inicial
        $time_start = microtime(true);
        
        # Executa rotina interna
        $this->prepara();

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        # Verifica se é detalhado
        if ($this->detalhado) {
            $situacao = "get_situacao";
        } else {
            $situacao = "get_situacaoRel";
        }

        # Dados da Tabela
        if (($this->situacao == 1) AND ($this->situacaoSinal == "=")) {
            $label = array("ID/Matrícula", "Servidor", "Cargo - Função (Comissão)", "Lotação", "Perfil", "Admissão", "Situação");
            $width = array(8, 20, 20, 18, 14, 5, 5);
            $function = array(null, null, null, null, null, "date_to_php", $situacao);
        } else {
            $label = array("ID/Matrícula", "Servidor", "Cargo - Função (Comissão)", "Lotação", "Perfil", "Admissão", "Saída", "Situação");
            $width = array(8, 20, 20, 18, 14, 5, 5, 5);
            $function = array(null, null, null, null, null, "date_to_php", "date_to_php", $situacao);
        }

        $align = array("center", "left", "left", "left");
        $classe = array("pessoal", null, "pessoal", "pessoal", "pessoal");
        $metodo = array("get_idFuncionalEMatricula", null, "get_cargoComSalto", "get_lotacao", "get_perfil");

        # Executa o select juntando o selct e o select de paginacao
        $conteudo = $servidor->select($this->select . $this->selectPaginacao, true);

        if ($this->totReg == 0) {
            tituloTable($this->nomeLista);
            br();
            $callout = new Callout();
            $callout->abre();
            p('Nenhum item encontrado !!', 'center');
            $callout->fecha();
        } else {
            # Monta a tabela
            $tabela = new Tabela();

            $tabela->set_titulo($this->nomeLista);
            $tabela->set_conteudo($conteudo);
            $tabela->set_label($label);
            $tabela->set_width($width);
            $tabela->set_align($align);
            $tabela->set_classe($classe);
            $tabela->set_metodo($metodo);
            $tabela->set_funcao($function);
            $tabela->set_totalRegistro(true);
            $tabela->set_idCampo('idServidor');
            if ($this->permiteEditar) {
                $tabela->set_editar('servidor.php?fase=editar&id=');
            }

            if ($this->paginacao) {
                $tabela->set_rodape($this->texto . ' (' . $this->itemInicial . ' a ' . $this->itemFinal . ' de ' . $this->totReg . ' Registros)');
            }

            if (!is_null($this->matNomeId)) {
                $tabela->set_textoRessaltado($this->matNomeId);
            }

            $tabela->show();

            # Pega o time final
            $time_end = microtime(true);

            # Calcula e exibe o tempo
            $time = $time_end - $time_start;
            p(number_format($time, 4, '.', ',') . " segundos", "right", "f10");
        }
    }

    ###########################################################

    /**
     * Método relatorio
     * 
     * Exibe a lista
     *
     */
    public function showRelatorio() {
        # Executa rotina interna
        $this->prepara();

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        # Pega a quantidade de itens da lista
        $conteudo = $servidor->select($this->select, true);

        if (($this->situacao == 1) AND ($this->situacaoSinal == "=")) {
            $label = array("ID/Matrícula", "Servidor", "Cargo - Função (Comissão)", "Lotação", "Perfil", "Admissão", "Situação");
            $width = array(8, 20, 25, 23, 14, 5, 5);
            $function = array(null, null, null, null, null, "date_to_php", "get_situacaoRel");
        } else {
            $label = array("ID/Matrícula", "Servidor", "Cargo - Função (Comissão)", "Lotação", "Perfil", "Admissão", "Saída", "Situação");
            $width = array(8, 20, 25, 20, 14, 5, 5, 5);
            $function = array(null, null, null, null, null, "date_to_php", "date_to_php", "get_situacaoRel");
        }

        $align = array("center", "left", "left", "left");
        $classe = array("pessoal", null, "pessoal", "pessoal", "pessoal");
        $metodo = array("get_idFuncionalEMatricula", null, "get_cargoComSalto", "get_lotacao", "get_perfil");

        # Relatório
        $relatorio = new Relatorio();
        $relatorio->set_titulo("Servidores " . $this->titulo);
        if (!is_null($this->subTitulo)) {
            $relatorio->set_subtitulo($this->subTitulo);
        }

        $relatorio->set_label($label);
        $relatorio->set_width($width);
        $relatorio->set_align($align);
        $relatorio->set_funcao($function);
        $relatorio->set_classe($classe);
        $relatorio->set_metodo($metodo);
        $relatorio->set_subTotal(false);
        $relatorio->set_conteudo($conteudo);
        $relatorio->show();
    }

}
