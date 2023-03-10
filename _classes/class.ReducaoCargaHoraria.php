<?php

class ReducaoCargaHoraria {

    /**
     * Exibe as informações sobre a Redução da Carga Horária 
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
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
        $this->set_idServidor = $idServidor;
    }

    ###########################################################

    function get_dados($idReducao) {

        /**
         * Informe os dados de uma redução
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (vazio($idReducao)) {
            alert("É necessário informar o id da Redução.");
            return;
        }

        # Pega os dados
        $select = 'SELECT * ,
                          DATE_SUB((ADDDATE(dtInicio, INTERVAL periodo MONTH)),INTERVAL 1 DAY) dtTermino
                     FROM tbreducao
                    WHERE idReducao = ' . $idReducao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row;
    }

    ###########################################################

    function get_dadosAnterior($idReducao) {

        /**
         * Informe os dados de uma redução imediatamente anterior cronológicamente
         * 
         * @note Usado para para pegar os dados da solicitação anterior quando for renovação
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Inicia as variáveis
        $idReducaoAnterior = null;      // Guarda o idRedução imediatamente anterior
        $dadosAnterior = null;          // Guarda os dados da redução referentes a essa id anterior
        # Verifica se foi informado
        if (vazio($idReducao)) {
            alert("É necessário informar o id da Redução.");
            return;
        }

        # Pega o idServidor
        $dados = $this->get_dados($idReducao);
        $idServidor = $dados["idServidor"];

        # Com o IdServidor pega todas as reduções dele
        $select = "SELECT idReducao
                     FROM tbreducao
                    WHERE idServidor = $idServidor
                    ORDER BY dtSolicitacao";

        $row = $pessoal->select($select);

        # Percorre o array para encontrar o anterior
        foreach ($row as $redux) {
            if ($idReducao == $redux[0]) {    // Verifica se é a atual
                break;                      // Se for sai do loop 
            } else {
                $idReducaoAnterior = $redux[0]; // Atualiza a variável da id anterior
            }
        }

        # Pega os dados da redução anteior com o id encontrado
        $dadosAnterior = $this->get_dados($idReducaoAnterior);

        # Retorno
        return $dadosAnterior;
    }

    ###########################################################

    function get_numProcesso($idServidor = null) {

        /**
         * Informe o número do processo de solicitação de redução de carga horária de um servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (vazio($idServidor)) {
            $idServidor = $this->idServidor;
        }

        # Pega os dias publicados
        $select = "SELECT processoReducao
                     FROM tbservidor
                    WHERE idServidor = {$idServidor}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row[0];
    }

    ###########################################################

    function get_numProcessoAntigo($idServidor = null) {

        /**
         * Informe o número do processo Antigo de solicitação de redução de carga horária de um servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Verifica se foi informado
        if (vazio($idServidor)) {
            $idServidor = $this->idServidor;
        }

        # Pega os dias publicados
        $select = "SELECT processoAntigoReducao
                     FROM tbservidor
                    WHERE idServidor = {$idServidor}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return $row[0];
    }

    ###########################################################

    function get_numeroSolicitacoes() {

        /**
         * Informe o número de solicitações de redução de carga horária de um servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT idReducao
                     FROM tbreducao
                    WHERE idServidor = ' . $this->idServidor;

        $pessoal = new Pessoal();
        $row = $pessoal->count($select, false);

        # Retorno
        return $row[0];
    }

    ###########################################################

    function get_dataAtoReitor($idReducao) {

        /**
         * Informe a data do ato do reitor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT dtAtoReitor
                     FROM tbreducao
                    WHERE idReducao = ' . $idReducao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        return date_to_php($row[0]);
    }

    ###########################################################

    function get_ultimaSolicitacaoAberto() {

        /**
         * Informe o número de solicitações de redução de carga horária de um servidor
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT idReducao
                     FROM tbreducao
                    WHERE NOT arquivado AND idServidor = ' . $this->idServidor;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);
        $quantidade = $pessoal->count($select, false);

        # Retorno
        if ($quantidade > 0) {
            return $row[0];
        } else {
            return null;
        }
    }

    ###########################################################

    function get_dadosCiInicio($idReducao) {

        /**
         * fornece a próxima tarefa a ser realizada
         */
        # Pega os dados
        $select = "SELECT numCiInicio,
                        dtCiInicio,
                        dtInicio,
                        dtPublicacao,
                        pgPublicacao,
                        periodo
                   FROM tbreducao
                  WHERE idReducao = $idReducao";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados;
    }

    ###########################################################

    function get_dadosCi45($idReducao) {

        /**
         * Informa os dados da ci de 45 dias (antiga 90 dias)
         */
        # Pega os dados
        $select = "SELECT numCi45,
                        dtCi45,
                        dtPublicacao,
                        pgPublicacao,
                        DATE_SUB((ADDDATE(dtInicio, INTERVAL periodo MONTH)),INTERVAL 1 DAY)
                   FROM tbreducao
                  WHERE idReducao = $idReducao";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados;
    }

    ###########################################################

    function get_dadosReducao($idReducao) {

        /**
         * fornece a próxima tarefa a ser realizada
         */
        # Pega os dados
        $select = "SELECT *
                   FROM tbreducao
                  WHERE idReducao = $idReducao";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados;
    }

    ###########################################################

    function get_dadosCiTermino($idReducao) {

        /**
         * fornece a próxima tarefa a ser realizada
         */
        # Pega os dados
        $select = "SELECT numCitermino,
                        dtCiTermino,
                        dtInicio,
                        dtPublicacao,
                        pgPublicacao,
                        periodo,
                        ADDDATE(dtInicio,INTERVAL periodo MONTH)
                   FROM tbreducao
                  WHERE idReducao = $idReducao";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados;
    }

    ###########################################################

    function exibePeriodo($idReducao) {

        /**
         * Informe os dados da período de uma solicitação de redução de carga horária específica
         * 
         * @obs Usada na tabela inicial do cadastro de redução
         */
        # Pega os Dados
        $dados = $this->get_dados($idReducao);

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT dtInicio, 
                          periodo,
                          DATE_SUB((ADDDATE(dtInicio, INTERVAL periodo MONTH)),INTERVAL 1 DAY),
                          resultado
                     FROM tbreducao
                    WHERE idReducao = ' . $idReducao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

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

            # Verifica se estamos a 31 dias da data Termino
            if (!vazio($dados["dtTermino"])) {
                $hoje = date("d/m/Y");
                $dias = dataDif($hoje, $dttermino);

                if (($dias > 0) AND ($dias < 45)) {
                    if ($dias == 1) {
                        $retorno .= "<br/><span title='Falta apenas $dias dia para o término do benefício. Entrar em contato com o servidor para avaliar renovação do benefício!' class='warning label'>Falta apenas $dias dia</span>";
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

    function exibeCi($idReducao) {

        /**
         * Informe os dados da CI de uma solicitação de redução de carga horária específica
         * 
         * @obs Usada na tabela inicial do cadastro de redução
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT numCiInicio, numCiTermino, resultado
                     FROM tbreducao
                    WHERE idReducao = ' . $idReducao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        if ($row[2] == 1) {
            $retorno = "CI Início  : " . trataNulo($row[0]) . "<br/>"
                    . "CI Término : " . trataNulo($row[1]);
        } else {
            $retorno = null;
        }

        return $retorno;
    }

    ###########################################################

    function exibeBotaoDocumentos($idReducao) {

        /**
         * Exibe o botão de imprimir os documentos de uma solicitação de redução de carga horária específica
         * 
         * @obs Usada na tabela inicial do cadastro de redução
         */
        # Pega os dados
        $dados = $this->get_dados($idReducao);

        # Pega os dados
        $resultado = $dados["resultado"];
        $atoReitor = date_to_php($dados["dtAtoReitor"]);
        $dtTermino = date_to_php($dados["dtTermino"]);

        # Inicia o menu
        $menu = new Menu("menuBeneficios");

        # Despacho: Ciência do Indeferimento por Inquérito
        if ($resultado == 2) {
            $menu->add_item('linkWindow', "\u{1F5A8} Despacho: Ciência do Indeferimento por Inquérito", '?fase=despachoCienciaIndeferimentoInquerito');
        }

        # Despacho para Perícia
        $menu->add_item('linkWindow', "\u{1F5A8} Despacho Para Perícia", '?fase=despachoPericia&id=' . $idReducao);

        # Despacho: Ciência do Indeferimento
        if ($resultado == 2) {
            $menu->add_item('linkWindow', "\u{1F5A8} Despacho: Ciência do Indeferimento", '?fase=despachoCienciaIndeferimento&id=' . $idReducao);
        }

        # Ato do Reitor
        if ($resultado == 1) {
            $nomeBotaoAto = "Ato do Reitor";

            if (!is_null($atoReitor)) {
                $nomeBotaoAto = "Ato do Reitor " . $atoReitor;
            }
            $menu->add_item('link', "\u{1F5A8} " . $nomeBotaoAto, '?fase=atoReitor&id=' . $idReducao);
        }

//        # Despacho à Reitoria
//        if ($resultado == 1) {
//            $menu->add_item('linkWindow', "\u{1F5A8} Despacho à Reitoria", '?fase=despachoReitoria');
//        }
        # Despacho para Publicação
        $menu->add_item('linkWindow', "\u{1F5A8} Despacho para Publicação", '?fase=despachoPublicacao');

        if ($resultado == 1) {

            $hoje = date("d/m/Y");
            $dias = dataDif($hoje, $dtTermino);

            # Despacho: Início da Concessão
            $menu->add_item('linkWindow', "\u{1F5A8} Despacho: Início da Concessão", '?fase=despachoInicio&id=' . $idReducao);

            # Despacho: Aviso 45 Dias
            if (abs($dias) <= 45) {
                $menu->add_item('linkWindow', "\u{1F5A8} Despacho: Aviso 45 Dias", '?fase=despacho45dias&id=' . $idReducao);
            }

            # Despacho: Aviso de Término
            $menu->add_item('linkWindow', "\u{1F5A8} Despacho: Aviso de Término", '?fase=despachoTermino&id=' . $idReducao);
        }

        # Despacho de conclusão temporária
        $menu->add_item('linkWindow', "\u{1F5A8} Despacho de Conclusão Temporária", '?fase=despachoConclusaoTemporaria');

        $menu->show();
    }

    ###########################################################

    function exibePublicacao($idReducao) {

        /**
         * Informe os dados da Publicação de uma solicitação de redução de carga horária específica
         * 
         * @obs Usada na tabela inicial do cadastro de redução
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT dtPublicacao, pgPublicacao, resultado
                     FROM tbreducao
                    WHERE idReducao = ' . $idReducao;

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

    function exibeResultado($idReducao) {

        /**
         * Informe os dados do resultado de uma solicitação de redução de carga horária específica
         * 
         * @obs Usada na tabela inicial do cadastro de redução
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT resultado
                     FROM tbreducao
                    WHERE idReducao = ' . $idReducao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);
        $retorno = null;

        # Verifica o resultado
        switch ($row["resultado"]) {
            case null:
                $retorno = null;
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

        return $retorno;
    }

    ###########################################################

    function exibeStatus($idReducao) {

        /**
         * Informe o status de uma solicitação de redução de carga horária específica
         * 
         * @obs Usada na tabela inicial do cadastro de redução
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = 'SELECT status
                     FROM tbreducao
                    WHERE idReducao = ' . $idReducao;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);
        $retorno = null;

        # Verifica o status
        switch ($row["status"]) {
            case 1:
                $retorno = "Em Aberto";
                break;

            case 2:
                $retorno = "Vigente";
                break;

            case 3:
                $retorno = "Arquivado";
                break;
        }

        return $retorno;
    }

    ###########################################################

    function mudaStatus() {

        /** 	
         * Função que altera o status de acordo com o resultado
         * 
         * Caso
         * resultado: null                                  -> status: 1 (Em aberto)
         * resultado: 1 (deferido) e data final não passou  -> status: 2 (Vigente)
         * resultado: 1 (deferido) e data final já passou   -> status: 3 (Arquivado)
         * resultado: 2 (indeferido)                        -> status: 3 (Arquivado)
         */
        # Conecta
        $pessoal = new Pessoal();

        /*
         * resultado: null -> status: 1 (Em aberto)
         */
        $sql = 'UPDATE tbreducao SET status = 1
                 WHERE resultado IS NULL';

        $pessoal->update($sql);

        /*
         * resultado: 1 (deferido) e data final não passou-> status: 2 (Vigente)
         */
        $sql = 'UPDATE tbreducao SET status = 2
                 WHERE resultado = 1
                   AND ADDDATE(dtInicio,INTERVAL periodo MONTH) > CURDATE()';

        $pessoal->update($sql);

        /*
         * origem = 2(solicitado) e resultado: 1 (deferido) e data final já passou -> status: 3 (Arquivado)
         */
        $sql = 'UPDATE tbreducao SET status = 3
                 WHERE resultado = 1
                   AND ADDDATE(dtInicio,INTERVAL periodo MONTH) < CURDATE()';

        $pessoal->update($sql);

        /*
         * resultado: 2 (indeferido) -> status: 3 (Arquivado)
         */
        $sql = 'UPDATE tbreducao SET status = 3
                 WHERE resultado = 2';

        $pessoal->update($sql);
    }

    ###########################################################
}
