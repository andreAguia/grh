<?php

class ListaFerias {

    /**
     * Exibe várias informações em forma de listas sobre as férias dos servidores
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $anoExercicio   integer null O Ano de exercícios das férias
     * @var private $lotacao        integer null O id da lotação. Quando null exibe de todas a universidade
     * @var private $permiteEditar  boolean true Indica se terá botão para acessar informções dos servidores
     */
    private $anoExercicio = null;
    private $lotacao = null;
    private $situacao = null;
    private $perfil = null;
    private $dias = null;
    private $permiteEditar = true;

    ###########################################################

    public function __construct($anoExercicio) {

        /**
         * Inicia a classe atribuindo um valor ao anoExercicio
         * 
         * @param $anoExercicio integer null O Ano de exercícios das férias
         */
        $this->anoExercicio = $anoExercicio;
    }

    ###########################################################

    public function set_lotacao($idLotacao = null) {
        /**
         * Informa a lotação dos servidores cujas ferias serão exibidas
         * 
         * @param $idLotacao integer null o idLotacão da lotação a ser exibida as férias
         * 
         * @note Quando o $idLotacao não é informado será exibido de todas as lotações.
         * 
         * @syntax $ListaFerias->set_lotacao([$idLotacao]);  
         */
        # Força a ser nulo mesmo quando for ""
        if (empty($idLotacao)) {
            $idLotacao = null;
        }

        # Transforma em nulo a máscara *
        if ($idLotacao == "*") {
            $idLotacao = null;
        }

        $this->lotacao = $idLotacao;
    }

    ###########################################################

    public function set_situacao($situacao = null) {
        /**
         * Informa a situacao dos servidores cujas ferias serão exibidas
         * 
         * @param $situacao integer null o idSituacao da situacao a ser exibida as férias
         * 
         * @note Quando o $situacao não é informado será exibido todas situacões
         * 
         * @syntax $ListaFerias->set_situacao([$situacao]);  
         */
        # Força a ser nulo mesmo quando for ""
        if (empty($situacao)) {
            $situacao = null;
        }

        # Transforma em nulo a máscara *
        if ($situacao == "*") {
            $situacao = null;
        }

        $this->situacao = $situacao;
    }

    ###########################################################

    public function set_perfil($perfil = null) {
        /**
         * Informa o perfil dos servidores cujas ferias serão exibidas
         * 
         * @param $perfil integer null o id do perfil a ser exibida as férias
         * 
         * @note Quando o $perfil não é informado será exibido todos os perfis
         * 
         * @syntax $ListaFerias->set_perfil([$perfil]);  
         */
        # Força a ser nulo mesmo quando for ""
        if (empty($perfil)) {
            $perfil = null;
        }

        # Transforma em nulo a máscara *
        if ($perfil == "*") {
            $perfil = null;
        }

        $this->perfil = $perfil;
    }

    ###########################################################

    public function set_dias($dias = null) {
        /**
         * Informa o total de dias a serem exibidas
         * 
         * @param $dias integer null o total de dias
         * 
         * @note Quando o $dias não é informado será exibido todos os dias
         * 
         * @syntax $ListaFerias->set_dias([$dias]);  
         */
        if ($dias <> 0) {
            # Força a ser nulo mesmo quando for ""        
            if (empty($dias)) {
                $dias = null;
            }

            # Transforma em nulo o texto Todos
            if ($dias == "Todos") {
                $dias = null;
            }

            # Transforma em nulo a máscara *
            if ($dias == "*") {
                $dias = null;
            }
        }

        $this->dias = $dias;
    }

    ###########################################################

    public function showResumoGeral() {

        /**
         * Informa os totais de servidores do setor com ou sem férias
         * 
         * @syntax $ListaFerias->showResumoGeral();  
         *
         */
        # Servidores desse setor que solicitaram férias
        $servset1 = $this->getServidoresComTotalDiasFerias();   // Os que pediram férias
        $totalServidores1 = count($servset1);

        # Servidores desse setor que NÃO solicitaram férias
        $semFerias = array();                           // Array dos servidores sem férias    

        if ($this->situacao == 1 OR is_null($this->situacao)) {
            $servset2 = $this->getServidoresSemFerias();    // Os que não pediram férias
            $totalServidores2 = count($servset2);
        } else {
            $totalServidores2 = 0;
        }
        $semFerias[] = ["Solicitaram", $totalServidores1];
        $semFerias[] = ["Não Solicitaram", $totalServidores2];
        $totalServidores3 = $totalServidores1 + $totalServidores2;

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($semFerias);
        $tabela->set_label(["Descrição", "Nº de Servidores"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_align(["center"]);
        $tabela->set_titulo("Resumo Geral");
        $tabela->set_rodape("Total de Servidores: " . $totalServidores3);
        $tabela->show();

        # Coloca no array para exibição o número de servidores sem ferias
    }

    ###########################################################

    public function showResumoPorDia() {

        /**
         * Informa os totais de servidores que solicitaram férias por total de dias solicitados
         * 
         * @syntax $ListaFerias->showResumoPorDia();  
         *
         */
        # Pega um array com os totais dos dias de férias dessa lotação nesse anoexercicio
        $diasTotais = $this->getDiasFerias();

        # Conta o número de dias 
        $totalFerias = count($diasTotais);

        # Array para exibir na tela   
        $conta = array();

        # Totalizador de servidores que pediram férias
        $tt = 0;

        # Informa quantos servidores em cada total de dias
        if ($totalFerias > 0) {
            $conta = $this->getTotalServidorDiasFerias($diasTotais);

            # Soma os servidores que periram férias nesse exercício e nessa lotação
            foreach ($conta as $contaSomada) {
                $tt += $contaSomada[0];
            }
        }

        # Exibe os servidores desse setor que solicitaram férias
        $servset = $this->getServidoresComTotalDiasFerias();   // Os que pediram férias
        $totalServidores = count($servset);                    // Conta o número de servidores

        $totalSem = $this->getNumServidoresSemFerias();
        if ($totalSem > 0) {
            array_push($conta, array("0", $totalSem));
            $totalServidores += $totalSem;
        }

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($conta);
        $tabela->set_label(["Dias", "Servidores"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_align(["center"]);
        $tabela->set_titulo("Servidores Por Dia");
        $tabela->set_rodape("Total de Servidores: " . $totalServidores);
        $tabela->show();
    }

    ###########################################################

    /**
     * Método showPorDia
     * 
     * Exibe um resumo geral das férias por lotação
     *
     */
    public function showPorDia() {

        # Exibe os servidores desse setor
        $servset1 = $this->getServidoresComTotalDiasFerias();   // Os que pediram férias
        $servset2 = $this->getServidoresSemFerias();            // Os que não pediram férias

        if ($this->situacao == 1 OR is_null($this->situacao)) {

            IF (is_null($this->dias)) {
                $resultado = array_merge_recursive($servset2, $servset1); // Junta os dois
            } else {
                if ($this->dias == 0) {
                    $resultado = $servset2;
                } else {
                    $resultado = $servset1;
                }
            }
        } else {
            $resultado = $servset1;
        }

        # Pega o tatal de servidores
        $totalServidores = count($resultado);

        # Monta a tabela de Servidores.
        if ($totalServidores > 0) {

            # Ordena o array
            $resultadoOrdenado = array_sort($resultado, 'nome', SORT_ASC);

            $tabela = new Tabela();
            $tabela->set_titulo("Ano Exercício: " . $this->anoExercicio);
            $tabela->set_subtitulo("Agrupados pelo Total de Dias");
            $tabela->set_label(["Id", "Servidor", "Lotação", "Admissão", "Dias", "Situação", "Pendências"]);
            $tabela->set_classe([null, "pessoal", "pessoal", null, null, null, "Ferias"]);

            # Exibe o órgão quando for cedido
            if ($this->lotacao == 113) {
                $tabela->set_metodo([null, "get_nomeECargoELotacaoEPerfil", "get_lotacaoSimples", null, null, null, "exibeFeriasPendentes"]);
            } else {
                $tabela->set_metodo([null, "get_nomeECargoEPerfil", "get_lotacaoSimples", null, null, null, "exibeFeriasPendentes"]);
            }

            $tabela->set_funcao([null, null, null, "date_to_php", null, "get_situacao"]);
            $tabela->set_align(["center", "left", "left"]);
            $tabela->set_idCampo('idServidor');
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 4,
                    'valor' => 30,
                    'operador' => '>',
                    'id' => 'problemas'),
                array('coluna' => 4,
                    'valor' => 30,
                    'operador' => '=',
                    'id' => 'certo'),
                array('coluna' => 4,
                    'valor' => 30,
                    'operador' => '<',
                    'id' => 'faltando')));

            if ($this->permiteEditar) {
                $tabela->set_editar('?fase=editaServidorFerias&id=');
                $tabela->set_nomeColunaEditar("Acessar");
                $tabela->set_editarBotao("olho.png");
            }

            $tabela->set_conteudo($resultadoOrdenado);
            $tabela->show();
        }
    }

    ###########################################################

    /**
     * Método getDiasFerias
     * 
     * Informa os totais de dias de férias de uma determinada lotação de uma ano exercício
     *
     */
    private function getDiasFerias($idLotacao = null) {
        # Conecta com o banco de dados
        $servidor = new Pessoal();

        # Pega os dias totais desse exercício/Lotação
        $select = "SELECT distinct sum(numDias) as soma
                     FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                LEFT JOIN tbferias USING (idServidor)
                     JOIN tbhistlot USING (idServidor)
                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE anoExercicio = {$this->anoExercicio}
                      AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

        # Verifica se tem filtro por lotação
        if (!is_null($idLotacao)) { // dá prioridade ao filtro da função
            if (is_numeric($idLotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$idLotacao})";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$idLotacao}')";
            }
        } elseif (!is_null($this->lotacao)) {  // senão verifica o da classe
            if (is_numeric($this->lotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$this->lotacao})";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$this->lotacao}')";
            }
        }

        # Verifica se tem filtro por situação
        if (!is_null($this->situacao)) {
            $select .= " AND situacao = {$this->situacao}";
        }

        # Verifica se tem filtro por perfil
        if (!is_null($this->perfil)) {
            $select .= " AND idPerfil = {$this->perfil}";
        }

        $select .= " GROUP BY idServidor
                     ORDER BY soma desc";

        $diasTotais = $servidor->select($select);
        return $diasTotais;
    }

    ###########################################################

    /**
     * Método getTotalServidorDiasFerias
     * 
     * Informa array com os totais de servidores pelo total de dias de férias de uma determinada lotação de uma ano exercício
     *
     */
    private function getTotalServidorDiasFerias($diasTotais) {
        # Conecta com o banco de dados
        $servidor = new Pessoal();

        foreach ($diasTotais as $valor) {
            $select = "SELECT idServidor,
                              sum(numDias) as soma
                         FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                       LEFT JOIN tbferias USING (idServidor)
                                            JOIN tbhistlot USING (idServidor)
                                            JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE anoExercicio = {$this->anoExercicio} 
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

            if (!is_null($this->lotacao)) {
                if (is_numeric($this->lotacao)) {
                    $select .= " AND (tblotacao.idlotacao = {$this->lotacao})";
                } else { # senão é uma diretoria genérica
                    $select .= " AND (tblotacao.DIR = '{$this->lotacao}')";
                }
            }

            # Verifica se tem filtro por situação
            if (!is_null($this->situacao)) {
                $select .= " AND situacao = {$this->situacao}";
            }

            # Verifica se tem filtro por perfil
            if (!is_null($this->perfil)) {
                $select .= " AND idPerfil = {$this->perfil}";
            }

            $select .= " GROUP BY idServidor
                     HAVING soma = $valor[0]
                     ORDER BY 1";

            $num = $servidor->count($select);
            $conta[] = array($valor[0], $num);
        }

        return $conta;
    }

    ###########################################################

    /**
     * Método getServidoresComTotalDiasFerias
     * 
     * Informa array com todos os servidores que pediram férias desse setor e o total de dias
     *
     */
    private function getServidoresComTotalDiasFerias() {
        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $select = "(SELECT tbservidor.idFuncional,
                            tbservidor.idServidor,
                            tbservidor.idServidor,
                            tbservidor.dtAdmissao,
                            sum(numDias) as soma,
                            tbservidor.idServidor,
                            tbservidor.idServidor,
                            tbpessoa.nome as nome
                       FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                     LEFT JOIN tbferias USING (idServidor)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       ";

        # Verifica se tem filtro por lotação
        if (!is_null($this->lotacao)) {
            if (is_numeric($this->lotacao)) {
                $select .= " AND (tblotacao.idlotacao = {$this->lotacao})";
            } else { # senão é uma diretoria genérica
                $select .= " AND (tblotacao.DIR = '{$this->lotacao}')";
            }
        }

        # Verifica se tem filtro por situação
        if (!is_null($this->situacao)) {
            $select .= " AND situacao = {$this->situacao}";
        }

        # Verifica se tem filtro por perfil
        if (!is_null($this->perfil)) {
            $select .= " AND idPerfil = {$this->perfil}";
        }

        $select .= "
              AND anoExercicio = $this->anoExercicio
        GROUP BY tbpessoa.nome";

        # dias
        if (!is_null($this->dias)) {
            $select .= " HAVING soma = {$this->dias}";
        }

        $select .= " ORDER BY soma,tbpessoa.nome)";

        # Pega os dados do banco
        $retorno = $servidor->select($select, true);

        return $retorno;
    }

    ###########################################################

    /**
     * Método getServidoresSemFerias
     * 
     * Informa array com todos os servidores que não pediram férias desse setor
     *
     */
    private function getServidoresSemFerias() {
        # Varifica se a situação é ativo ou todos
        if ($this->situacao == 1 OR is_null($this->situacao)) {

            # Conecta com o banco de dados
            $servidor = new Pessoal();

            $select2 = "SELECT tbservidor.idFuncional,
                            tbservidor.idServidor,
                           tbservidor.idServidor,
                           tbservidor.dtAdmissao,
                           '-',
                           tbservidor.idServidor,
                           tbservidor.idServidor,
                           tbpessoa.nome as nome
                      FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tbperfil USING (idPerfil)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND YEAR(tbservidor.dtAdmissao) < {$this->anoExercicio}
                      ";

            # Verifica se tem filtro por lotação
            if (!is_null($this->lotacao)) {  // senão verifica o da classe
                if (is_numeric($this->lotacao)) {
                    $select2 .= " AND (tblotacao.idlotacao = {$this->lotacao})";
                } else { # senão é uma diretoria genérica
                    $select2 .= " AND (tblotacao.DIR = '{$this->lotacao}')";
                }
            }

            # Verifica se tem filtro por perfil
            if (is_null($this->perfil)) {
                $select2 .= " AND tbperfil.tipo <> 'Outros'";
            } else {
                $select2 .= " AND idPerfil = {$this->perfil}";
            }

            $select2 .= "
             AND tbservidor.situacao = 1
             AND tbpessoa.nome NOT IN 
             (SELECT tbpessoa.nome
             FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                JOIN tbferias USING (idservidor)
                                JOIN tbhistlot USING (idServidor)
                                JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
            WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND anoExercicio = {$this->anoExercicio}";

            if (!is_null($this->lotacao)) {

                if (is_numeric($this->lotacao)) {
                    $select2 .= " AND (tblotacao.idlotacao = {$this->lotacao})";
                } else { # senão é uma diretoria genérica
                    $select2 .= " AND (tblotacao.DIR = '{$this->lotacao}')";
                }
            }

            # Verifica se tem filtro por perfil
            if (!is_null($this->perfil)) {
                $select2 .= " AND idPerfil = {$this->perfil}";
            }

            $select2 .= "
                AND tbservidor.situacao = 1
           ORDER BY tbpessoa.nome asc)
              ORDER BY tbpessoa.nome asc";

            # retorna o array
            return $servidor->select($select2, true);
        }
    }

    ###########################################################

    /**
     * Método getNumServidoresSemFerias
     * 
     * Informa numero de servidores que não pediram férias desse setor
     *
     */
    private function getNumServidoresSemFerias() {
        # Varifica se a situação é ativo ou todos
        if ($this->situacao == 1 OR is_null($this->situacao)) {

            # Conecta com o banco de dados
            $servidor = new Pessoal();

            $select2 = "SELECT tbservidor.idFuncional,
                            tbservidor.idServidor,
                           tbservidor.idServidor,
                           tbservidor.dtAdmissao,
                           '-',
                           tbservidor.idServidor,
                           tbservidor.idServidor,
                           tbpessoa.nome as nome
                      FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tbperfil USING (idPerfil)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND YEAR(tbservidor.dtAdmissao) < {$this->anoExercicio}
                      ";

            # Verifica se tem filtro por lotação
            if (!is_null($this->lotacao)) {  // senão verifica o da classe
                if (is_numeric($this->lotacao)) {
                    $select2 .= " AND (tblotacao.idlotacao = {$this->lotacao})";
                } else { # senão é uma diretoria genérica
                    $select2 .= " AND (tblotacao.DIR = '{$this->lotacao}')";
                }
            }

            # Verifica se tem filtro por perfil
            if (is_null($this->perfil)) {
                $select2 .= " AND tbperfil.tipo <> 'Outros'";
            } else {
                $select2 .= " AND idPerfil = {$this->perfil}";
            }

            $select2 .= "
             AND tbservidor.situacao = 1
             AND tbpessoa.nome NOT IN 
             (SELECT tbpessoa.nome
             FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                JOIN tbferias USING (idservidor)
                                JOIN tbhistlot USING (idServidor)
                                JOIN tblotacao ON (tbhistlot.lotacao = tblotacao.idLotacao)
            WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                  AND anoExercicio = {$this->anoExercicio}";

            if (!is_null($this->lotacao)) {

                if (is_numeric($this->lotacao)) {
                    $select2 .= " AND (tblotacao.idlotacao = {$this->lotacao})";
                } else { # senão é uma diretoria genérica
                    $select2 .= " AND (tblotacao.DIR = '{$this->lotacao}')";
                }
            }

            # Verifica se tem filtro por perfil
            if (!is_null($this->perfil)) {
                $select2 .= " AND idPerfil = {$this->perfil}";
            }

            $select2 .= "
                AND tbservidor.situacao = 1
           ORDER BY tbpessoa.nome asc)
              ORDER BY tbpessoa.nome asc";

            # retorna o array
            return $servidor->count($select2);
        }
    }

    ###########################################################

    public function getArrayPorDia() {

        /**
         * Retorna um array simples com os totais de férias dos servidores de acordo com o filtro
         * 
         * @syntax $ListaFerias->showResumoPorDia();  
         *
         */
        # Pega um array com os totais dos dias de férias dessa lotação nesse anoexercicio
        $diasTotais = $this->getDiasFerias();
        $retorno = null;

        # Percorre o array e alimenta o array de retorno
        foreach ($diasTotais as $itens) {
            $retorno[] = $itens['soma'];
        }

        # Verifica se exite servidores que não marcou/tirou férias
        $totalSem = $this->getNumServidoresSemFerias();
        if ($totalSem > 0) {
            $retorno[] = "0";
        }

        return $retorno;
    }

    ###########################################################
}
