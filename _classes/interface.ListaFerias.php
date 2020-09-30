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
        if (vazio($idLotacao)) {
            $idLotacao = null;
        }

        # Transforma em nulo a máscara *
        if ($idLotacao == "*") {
            $idLotacao = null;
        }

        $this->lotacao = $idLotacao;
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
        $servset2 = $this->getServidoresSemFerias();    // Os que não pediram férias
        $totalServidores2 = count($servset2);
        $semFerias[] = array("Solicitaram", $totalServidores1);
        $semFerias[] = array("Não Solicitaram", $totalServidores2);
        $totalServidores3 = $totalServidores1 + $totalServidores2;

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($semFerias);
        $tabela->set_label(array("Descrição", "Nº de Servidores"));
        $tabela->set_totalRegistro(false);
        $tabela->set_align(array("center"));
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

        $conta = array();   // Array para exibir na tela    
        $tt = 0;            // Totalizador de servidores que pediram férias
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
        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($conta);
        $tabela->set_label(array("Dias", "Servidores"));
        $tabela->set_totalRegistro(false);
        $tabela->set_align(array("center"));
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

        # Pega um array com os totais dos dias de férias dessa lotação nesse anoexercicio
        $diasTotais = $this->getDiasFerias();

        # Conta o número de dias 
        $totalFerias = count($diasTotais);

        $conta = array();   // Array para exibir na tela    
        $tt = 0;            // Totalizador de servidores que pediram férias
        # Informa quantos servidores em cada total de dias
        if ($totalFerias > 0) {
            $conta = $this->getTotalServidorDiasFerias($diasTotais);

            # Soma os servidores que periram férias nesse exercício e nessa lotação
            foreach ($conta as $contaSomada) {
                $tt += $contaSomada[0];
            }
        }

        # Exibe os servidores desse setor
        $servset1 = $this->getServidoresComTotalDiasFerias();   // Os que pediram férias
        $servset2 = $this->getServidoresSemFerias();            // Os que não pediram férias
        $servset3 = array_merge_recursive($servset2, $servset1); // Junta os dois
        $totalServidores = count($servset3);                    // Conta o número de servidores
        # Monta a tabela de Servidores.
        if ($totalServidores > 0) {

            $tabela = new Tabela();
            $tabela->set_titulo("Ano Exercício: " . $this->anoExercicio);
            $tabela->set_label(array("Id", "Servidor", "Lotação", "Perfil", "Admissão", "Dias", "Situação"));
            $tabela->set_classe(array(null, null, "pessoal", "pessoal"));
            $tabela->set_metodo(array(null, null, "get_lotacaoSimples", "get_perfilSimples"));
            $tabela->set_funcao(array(null, null, null, null, "date_to_php",null, "get_situacao"));
            $tabela->set_align(array("center", "left", "left"));
            $tabela->set_idCampo('idServidor');
            $tabela->set_formatacaoCondicional(array(
                array('coluna' => 5,
                    'valor' => 30,
                    'operador' => '>',
                    'id' => 'problemas'),
                array('coluna' => 5,
                    'valor' => 30,
                    'operador' => '=',
                    'id' => 'certo'),
                array('coluna' => 5,
                    'valor' => 30,
                    'operador' => '<',
                    'id' => 'faltando')));

            if ($this->permiteEditar) {
                $tabela->set_editar('?fase=editaServidorFerias&id=');
                $tabela->set_nomeColunaEditar("Acessar");
                $tabela->set_editarBotao("ver.png");
            }

            $tabela->set_conteudo($servset3);
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
        $slctot = "SELECT distinct sum(numDias) as soma
                     FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                LEFT JOIN tbferias USING (idServidor)
                     JOIN tbhistlot USING (idServidor)
                     JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                    WHERE anoExercicio = $this->anoExercicio
                      AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

        # Verifica se tem filtro por lotação
        if (!is_null($idLotacao)) { // dá prioridade ao filtro da função
            if (is_numeric($idLotacao)) {
                $slctot .= ' AND (tblotacao.idlotacao = "' . $idLotacao . '")';
            } else { # senão é uma diretoria genérica
                $slctot .= ' AND (tblotacao.DIR = "' . $idLotacao . '")';
            }
        } elseif (!is_null($this->lotacao)) {  // senão verifica o da classe
            if (is_numeric($this->lotacao)) {
                $slctot .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $slctot .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        $slctot .= "GROUP BY idServidor
                    ORDER BY soma desc";

        $diasTotais = $servidor->select($slctot);
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
            $slctot = "SELECT idServidor,
                              sum(numDias) as soma
                         FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                       LEFT JOIN tbferias USING (idServidor)
                                            JOIN tbhistlot USING (idServidor)
                                            JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                        WHERE anoExercicio = $this->anoExercicio 
                          AND tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)";

            if (!is_null($this->lotacao)) {
                if (is_numeric($this->lotacao)) {
                    $slctot .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
                } else { # senão é uma diretoria genérica
                    $slctot .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
                }
            }

            $slctot .= "
                     GROUP BY idServidor
                     HAVING soma = $valor[0]
                     ORDER BY 1";
            $num = $servidor->count($slctot);
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

        $select1 = "(SELECT tbservidor.idFuncional,
                            tbpessoa.nome,
                            tbservidor.idServidor,
                            tbservidor.idServidor,
                            tbservidor.dtAdmissao,
                            sum(numDias) as soma,
                            tbservidor.idServidor
                       FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                     LEFT JOIN tbferias USING (idServidor)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                       ";

        # Verifica se tem filtro por lotação
        if (!is_null($this->lotacao)) {  // senão verifica o da classe
            if (is_numeric($this->lotacao)) {
                $select1 .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select1 .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        $select1 .= "
              AND anoExercicio = $this->anoExercicio
        GROUP BY tbpessoa.nome
         ORDER BY soma,tbpessoa.nome)";

        # Pega os dados do banco
        $retorno = $servidor->select($select1, true);

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
        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $select2 = "SELECT tbservidor.idFuncional,
                           tbpessoa.nome,
                           tbservidor.idServidor,
                           tbservidor.idServidor,
                           tbservidor.dtAdmissao,
                           '-',
                           tbservidor.idServidor
                      FROM tbpessoa LEFT JOIN tbservidor USING (idPessoa)
                                         JOIN tbhistlot USING (idServidor)
                                         JOIN tblotacao ON (tbhistlot.lotacao=tblotacao.idLotacao)
                     WHERE tbhistlot.data = (select max(data) from tbhistlot where tbhistlot.idServidor = tbservidor.idServidor)
                     AND YEAR(tbservidor.dtAdmissao) < $this->anoExercicio
                      ";

        # Verifica se tem filtro por lotação
        if (!is_null($this->lotacao)) {  // senão verifica o da classe
            if (is_numeric($this->lotacao)) {
                $select2 .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select2 .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
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
                  AND anoExercicio = $this->anoExercicio";

        if (!is_null($this->lotacao)) {

            if (is_numeric($this->lotacao)) {
                $select2 .= ' AND (tblotacao.idlotacao = "' . $this->lotacao . '")';
            } else { # senão é uma diretoria genérica
                $select2 .= ' AND (tblotacao.DIR = "' . $this->lotacao . '")';
            }
        }

        $select2 .= "
                AND tbservidor.situacao = 1
           ORDER BY tbpessoa.nome asc)
              ORDER BY tbpessoa.nome asc";

        # Pega os dados do banco
        $retorno = $servidor->select($select2, true);

        return $retorno;
    }

}
