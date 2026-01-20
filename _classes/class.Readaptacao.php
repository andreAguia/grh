<?php

class Readaptacao {

    /**
     * Exibe as informações sobre a Readaptação de servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    private $idServidor = null;

    ###########################################################

    public function __construct($idServidor = null) {

        /**
         * Inicia a classe e preenche o idServidor
         */
        if (!is_null($idServidor)) {
            $this->idServidor = $idServidor;
        }
    }

    ###########################################################

    public function set_idServidor($idServidor) {
        /**
         * Informa o idServidor quando não se pode informar no instanciamento da classe
         *
         * @param $idServidor string null O idServidor
         *
         * @syntax $input->set_id($id);
         */
        $this->idServidor = $idServidor;
    }

    ###########################################################

    function get_dados($idReadaptacao) {

        /**
         * Informe o número do processo de solicitação de redução de carga horária de um servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (vazio($idReadaptacao)) {
            alert("É necessário informar o id da Readaptação.ttt");
            return;
        }

        # Pega os dados
        $select = 'SELECT * ,
                          DATE_SUB((ADDDATE(dtInicio, INTERVAL periodo MONTH)),INTERVAL 1 DAY) as dtTermino
                     FROM tbreadaptacao
                    WHERE idReadaptacao = ' . $idReadaptacao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row;
    }

    ###########################################################

    function exibeStatus($idReadaptacao) {

        /**
         * Informe o status de uma solicitação de redução de carga horária específica
         *
         * @obs Usada na tabela inicial do cadastro de redução
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT status, pendencia
                     FROM tbreadaptacao
                    WHERE idReadaptacao = ' . $idReadaptacao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);
        $retorno = null;

        # Verifica o status
        switch ($row[0]) {
            case 1:
                $retorno = "Em Aberto";
                break;

            case 2:
                $retorno = "Vigente";
                break;

            case 3:
                $retorno = "Arquivado";
                break;

            case 4:
                $retorno = "Aguardando Publicação";
                break;

            case 5:
                $retorno = "Interrompido";
                break;
        }

        return $retorno;
    }

    ###########################################################

    function exibeResultado($idReadaptacao) {

        /**
         * Informe os dados do resultado de uma solicitação de redução de carga horária específica
         *
         * @obs Usada na tabela inicial do cadastro de redução
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT resultado, pendencia
                     FROM tbreadaptacao
                    WHERE idReadaptacao = ' . $idReadaptacao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        $resultado = $row[0];
        $dataCiencia = $row[1];

        # Verifica o resultado
        switch ($resultado) {
            case null:
                $retorno = $resultado;
                break;

            case 1:
                $retorno = "Deferido";
                break;

            case 2:
                $retorno = "Indeferido";
                break;

            case 3:
                $retorno = "Interrompido";
                break;
        }

        # Verifica se há pendências
        if ($row[1] == 1) {
            $retorno .= "<br/><span title='Existem pendências nessa solicitação de redução de carga horária!' class='warning label'>Pendências</span>";
        }

        return $retorno;
    }

    ###########################################################

    function exibePublicacao($idReadaptacao) {

        /**
         * Informe os dados da Publicação de uma solicitação de redução de carga horária específica
         *
         * @obs Usada na tabela inicial do cadastro de redução
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT dtPublicacao, pgPublicacao, resultado
                     FROM tbreadaptacao
                    WHERE idReadaptacao = ' . $idReadaptacao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        if ($row[2] == 1) {
            if (empty($row[0])) {
                pLista("---");
            } else {
                pLista(
                        date_to_php($row[0]),
                        "pag: " . trataNulo($row[1])
                );
            }
        } else {
            return null;
        }
    }

    ###########################################################

    function exibePeriodo($idReadaptacao) {

        /**
         * Informe os dados da período de uma solicitação de redução de carga horária específica
         *
         * @obs Usada na tabela inicial do cadastro de redução
         */
        # Pega os Dados
        $dados = $this->get_dados($idReadaptacao);

        # Retorno
        if ($dados["resultado"] == 1) {

            # Trata a data de Início
            if (vazio($dados["dtInicio"])) {
                $dtInicio = "---";
            } else {
                $dtInicio = date_to_php($dados["dtInicio"]);
            }

            # Trata o período
            if (vazio($dados["periodo"])) {
                $periodo = "---";
            } else {
                $periodo = $dados["periodo"] . " m";
            }

            # Trata a data de término
            if (vazio($dados["dtTermino"])) {
                $dttermino = "---";
            } else {
                $dttermino = date_to_php($dados["dtTermino"]);
            }

            $retorno = "Início : " . $dtInicio . "<br/>"
                    . "Período: " . $periodo . "<br/>"
                    . "Término: " . $dttermino;

            # Verifica se estamos a 90 dias da data Termino
            if (!vazio($dados["dtTermino"])) {
                $hoje = date("d/m/Y");
                $dias = dataDif($hoje, $dttermino);

                if (($dias > 0) AND ($dias < 45)) {
                    if ($dias == 1) {
                        $retorno .= "<br/><span title='Falta Apenas $dias dia para o término do benefício. Entrar em contato com o servidor para avaliar renovação do benefício!' class='warning label'>Faltam $dias dias</span>";
                    } else {
                        $retorno .= "<br/><span title='Faltam $dias dias para o término do benefício. Entrar em contato com o servidor para avaliar renovação do benefício!' class='warning label'>Faltam $dias dias</span>";
                    }
                } elseif ($dias == 0) {
                    $retorno .= "<br/><span title='Hoje Termina o benefício!' class='warning label'>Termina Hoje!</span>";
                } elseif ($dias < 0) {
                    if ($dados["status"] == 2) {
                        $retorno .= "<br/><span title='Benefício terminou em {$dttermino}' class='alert label'>Já Terminou!</span>";
                    }
                }
            }
        } else {
            $retorno = null;
        }

        return $retorno;
    }

    ###########################################################

    function exibeBotaoDocumentos($idReadaptacao) {

        /**
         * Exibe o botão de imprimir os documentos de uma solicitação de redução de carga horária específica
         *
         * @obs Usada na tabela inicial do cadastro de redução
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT resultado,
                          numCiInicio,
                          numCiTermino,
                          numCi90,
                          DATE_SUB((ADDDATE(dtInicio, INTERVAL periodo MONTH)),INTERVAL 1 DAY),
                          dtInicio
                     FROM tbreadaptacao
                    WHERE idReadaptacao = ' . $idReadaptacao;

        $row = $pessoal->select($select, false);

        # Pega os dados
        $resultado = $row[0];
        $ciInicio = $row[1];
        $ciTermino = $row[2];
        $ci90 = $row[3];
        $dtTermino = date_to_php($row[4]);
        $dtInicio = date_to_php($row[5]);

        $dias = null;

        # Calcula os dias
        if (!is_null($dtTermino)) {
            $hoje = date("d/m/Y");
            $dias = dataDif($hoje, $dtTermino);
        }

        # Nome do botão de início
        $nomeBotaoInicio = "CI Início";
        if (!is_null($ciInicio)) {
            $nomeBotaoInicio = "CI Início n° " . $ciInicio;
        }

        $menu = new Menu("menuBeneficios");

        # Despachos
        $menu->add_item('linkWindow', "\u{1F5A8} Despacho Para Perícia", '?fase=despachoPerícia&id=' . $idReadaptacao);
        #$menu->add_item('linkWindow', "\u{1F5A8} Despacho: Início da Concessão", '?fase=despachoInicio&id=' . $idReadaptacao);
        # Retorno
        if (!vazio($dtInicio)) {

            # Ci Início
            $menu->add_item('link', "\u{1F5A8} " . $nomeBotaoInicio, '?fase=ciInicioForm&id=' . $idReadaptacao);

            # Despacho: Aviso de Término   
            $menu->add_item('linkWindow', "\u{1F5A8} Despacho: Aviso de Término", '?fase=despachoTermino&id=' . $idReadaptacao);
        }

        $menu->show();
    }

    ###########################################################

    function get_dadosAnterior($idReadaptacao) {

        /**
         * Informe os dados de uma Readaptacao imediatamente anterior cronológicamente
         * 
         * @note Usado para para pegar os dados da solicitação anterior quando for renovação
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Guarda o idRedução imediatamente anterior
        $idReadaptacaoAnterior = null;

        # Guarda os dados da redução referentes a essa id anterior
        $dadosAnterior = null;

        # Verifica se foi informado
        if (empty($idReadaptacao)) {
            alert("É necessário informar o id da readaptacao.");
            return null;
        }

        # Pega o idServidor
        $dados = $this->get_dados($idReadaptacao);
        $idServidor = $dados["idServidor"];

        # Com o IdServidor pega todas as reduções dele
        $select = "SELECT idReadaptacao
                     FROM tbreadaptacao
                    WHERE idServidor = {$idServidor}
                      AND dtInicio is not NULL  
                    ORDER BY idReadaptacao";

        $row = $pessoal->select($select);

        # Percorre o array para encontrar o anterior
        foreach ($row as $redux) {
            if ($idReadaptacao == $redux[0]) {    // Verifica se é a atual
                break;                          // Se for sai do loop 
            } else {
                $idReadaptacaoAnterior = $redux[0]; // Atualiza a variável da id anterior
            }
        }

        # Pega os dados da redução anteior com o id encontrado
        $dadosAnterior = $this->get_dados($idReadaptacaoAnterior);

        # Retorno
        return $dadosAnterior;
    }

    ##########################################################################################

    public function mudaStatus() {

        /** 	
         * Função que altera o status de acordo com o resultado
         * 
         * Caso 
         * origem = 2 (solicitado)
         * resultado: null          -> status: 1 (Em aberto)
         * resultado: 1 (deferido) e data final não passou-> status: 2 (Vigente)
         * resultado: 1 (deferido) e data final já passou -> status: 3 (Arquivado)
         * resultado: 2 (indeferido) -> status: 3 (Arquivado)
         * 
         * origem = 1 (Ex-ofício)  Não tem resultado
         * Se data de inicio e período é null -> status: 1 (Em aberto)
         * data final não passou-> status: 2 (Vigente)
         * data final já passou -> status: 3 (Arquivado)
         */
        # Conecta
        $pessoal = new Pessoal();

        /*
         * origem = 2(solicitado) e resultado: null -> status: 1 (Em aberto)
         */
        $sql = 'UPDATE tbreadaptacao SET status = 1
                 WHERE origem = 2
                   AND resultado IS NULL';

        $pessoal->update($sql);

        /*
         * origem = 2(solicitado) e resultado: 1 (deferido) e data final não passou-> status: 2 (Vigente)
         */
        $sql = 'UPDATE tbreadaptacao SET status = 2
                 WHERE origem = 2
                   AND resultado = 1
                   AND ADDDATE(dtInicio,INTERVAL periodo MONTH) > CURDATE()';

        $pessoal->update($sql);

        /*
         * origem = 2(solicitado) e resultado: 1 (deferido) e data final já passou -> status: 3 (Arquivado)
         */
        $sql = 'UPDATE tbreadaptacao SET status = 3
                 WHERE origem = 2
                   AND resultado = 1
                   AND ADDDATE(dtInicio,INTERVAL periodo MONTH) < CURDATE()';

        $pessoal->update($sql);

        /*
         * origem = 2(solicitado) e resultado: 2 (indeferido) -> status: 3 (Arquivado)
         */
        $sql = 'UPDATE tbreadaptacao SET status = 3
                 WHERE origem = 2
                   AND resultado = 2';

        $pessoal->update($sql);

        /*
         * origem = 1(ex-oficio) e Se data de inicio e período é null -> status: 1 (Em aberto)
         */
        $sql = 'UPDATE tbreadaptacao SET status = 1
                 WHERE origem = 1
                   AND dtInicio is NULL
                   AND periodo is NULL';

        $pessoal->update($sql);

        /*
         * origem = 1(ex-oficio) e Se data final não passou-> status: 2 (Vigente)
         */
        $sql = 'UPDATE tbreadaptacao SET status = 2
                 WHERE origem = 1
                   AND ADDDATE(dtInicio,INTERVAL periodo MONTH) > CURDATE()';

        $pessoal->update($sql);

        /*
         * origem = 1(ex-oficio) e Se data final já passou -> status: 3 (Arquivado)
         */
        $sql = 'UPDATE tbreadaptacao SET status = 3
                 WHERE origem = 1
                   AND ADDDATE(dtInicio,INTERVAL periodo MONTH) < CURDATE()';

        $pessoal->update($sql);
    }

    ###########################################################
}
