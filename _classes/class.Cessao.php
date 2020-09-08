<?php

class Cessao {

    /**
     * Abriga as várias rotina referentes a cessão de servidor da Uenf para outro órgão
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    ##############################################################

    public function getDados($idHistCessao = null) {
        # Verifica se foi informado
        if (vazio($idHistCessao)) {
            alert("É necessário informar o id.");
            return;
        }

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        # Pega os dados
        $select = "SELECT *
                     FROM tbhistcessao
                    WHERE idHistCessao = {$idHistCessao}";

        $row = $servidor->select($select, false);

        # Retorno
        return $row;
    }

######################################################################################################################

    public function exibeDados($idHistCessao) {

        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $select = "SELECT idHistCessao,
                          dtInicio,
                          dtFim,
                          orgao,
                          processo,
                          dtPublicacao,
                          obs
                     FROM tbhistcessao
                    WHERE idHistCessao = {$idHistCessao}";

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($servidor->select($select, true));
        $tabela->set_titulo("Dados da Cessão");
        $tabela->set_label(array("Status", "Data Inicial", "Data Final", "Órgão Cessionário", "Processo", "Data de Publicação", "Obs"));
        $tabela->set_funcao(array(null, "date_to_php", "date_to_php", null, null, "date_to_php"));
        $tabela->set_width(array(10, 10, 10, 25, 15, 10, 30));
        $tabela->set_align(array("center", "center", "center", "left", "left", "center", "left"));
        $tabela->set_totalRegistro(false);
        $tabela->set_classe(array("Cessao"));
        $tabela->set_metodo(array("getStatus"));

        # Pinta a tabela de cor diferente
        $dados = $this->getDados($idHistCessao);

        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 0,
                'valor' => "Vigente",
                'operador' => '=',
                'id' => 'cessaoVigente'),
            array('coluna' => 0,
                'valor' => "Terminada",
                'operador' => '=',
                'id' => 'cessaoTerminada')
        ));

        $tabela->show();

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

######################################################################################################################

    public function getDataInicialFrequencia($idHistCessao) {
        /*
         * Exibe o primeiro dia do mês da frequencia de cessão que ainda não foi atestada
         * Para o sistema sugerir no formulário de cadastro de frequência
         */

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        # Reserva uma variavel para a data escolhida
        $dataEscolhida = null;

        # Pega os Dados das Frequencias cadastradas desta Cessao
        $select = "SELECT dtFinal, idServidor FROM tbfrequencia WHERE idHistCessao = {$idHistCessao} ORDER BY dtFinal DESC";
        $dados = $servidor->select($select, false);

        # Verifica as frequencias
        if (empty($dados)) {
            # Nao tem nenhuma frequencia pega a data inicial da cessao
            $dadosCessao = $this->getDados($idHistCessao);
            $idServidor = $dadosCessao["idServidor"];
            $dataEscolhida = addDias(date_to_php($dadosCessao["dtInicio"]), 1, false);
        } else {
            $dataEscolhida = addDias(date_to_php($dados[0]), 1, false);
            $idServidor = $dados[1];
        }

        /*
         *  Verifica os afastamentos neste mes
         * 
         */
        #$verifica = new VerificaDadosAfastamentos($idServidor, $dataEscolhida, ultimoDiaMes($dataEscolhida));
        #$outro = $verifica->verifica();
        
        #echo count($outro);
        
        #if (!empty($outro)) {
        #    var_dump($outr0);
        #}
        
        #echo "oi";

        return $dataEscolhida;
    }

######################################################################################################################

    public function getDataFinalFrequencia($idHistCessao) {
        /*
         * Exibe o ultimo dia do mês da frequencia de cessão que ainda não foi atestada
         * Para o sistema sugerir no formulário de cadastro de frequência
         */

        # Pega o primeiro dia
        $primeiroDia = $this->getDataInicialFrequencia($idHistCessao);

        # retorna o ultimo dia
        return ultimoDiaMes($primeiroDia);
    }

###################################################################################################################################################################

    public function getStatus($idHistCessao) {
        /*
         * Exibe Vigente   - quando a cessao ainda esta vigente e
         *       Terminada - quando a cessao ja terminou
         */

        # Pega a data de termino da cessao
        $dados = $this->getDados($idHistCessao);
        $dtFim = $dados["dtFim"];

        if (empty($dtFim)) {
            return "Vigente";
        } else {
            $dtFim = date_to_php($dtFim);

            if (jaPassou($dtFim)) {
                return "Terminada";
            } else {
                return "Vigente";
            }
        }
    }

###############################################################################################################################################################################################
}
