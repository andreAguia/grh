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
        $tabela->set_label(["Status", "Data Inicial", "Data Final", "Órgão Cessionário", "Processo", "Data de Publicação", "Obs"]);
        $tabela->set_funcao([null, "date_to_php", "date_to_php", null, null, "date_to_php"]);
        $tabela->set_width([8, 8, 8, 10, 15, 8, 53]);
        $tabela->set_align(["center", "center", "center", "center", "center", "center", "left"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_classe(["Cessao"]);
        $tabela->set_metodo(["getStatus"]);

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
            $dataEscolhida = date_to_php($dadosCessao["dtInicio"]);
        } else {
            $dataEscolhida = addDias(date_to_php($dados[0]), 1, false);
            $idServidor = $dados[1];
        }
        
        /*
         * Verifica se tem algum afastamento este mês
         */
        
        $ultimoDia = ultimoDiaMes($dataEscolhida);
        $verificaDados = new VerificaDadosAfastamento($idServidor, $dataEscolhida, $ultimoDia);
        $verifica = $verificaDados->verifica();
                
        if(!empty($verifica[0][1])){
            
            if($dataEscolhida) {
            $dataEscolhida = addDias(date_to_php($verifica[0][1]), 1, false);
            }
        }
        
        return $dataEscolhida;
    }

######################################################################################################################

    public function getDataFinalFrequencia($idHistCessao) {
        /*
         * Exibe o ultimo dia do mês da frequencia de cessão que ainda não foi atestada
         * Para o sistema sugerir no formulário de cadastro de frequência
         */

        # Pega o primeiro dia disponível
        $primeiroDia = $this->getDataInicialFrequencia($idHistCessao);
        $ultimoDia = ultimoDiaMes($primeiroDia);

        /*
         *  Verifica se é esse o mês do término
         */
        $dados = $this->getDados($idHistCessao);

        if ((!empty($dados["dtFim"]))
                AND (month($primeiroDia) == month(date_to_php($dados["dtFim"])))
                AND (year($primeiroDia) == year(date_to_php($dados["dtFim"])))) {

            $ultimoDia = date_to_php($dados["dtFim"]);
        }
        
        /*
         * Verifica se tem algum afastamento este mês
         */
        
        $verificaDados = new VerificaDadosAfastamento($dados["idServidor"], $primeiroDia, $ultimoDia);
        $verifica = $verificaDados->verifica();
                
        if(!empty($verifica[0][0])){
            
            $ultimoDia = addDias(date_to_php($verifica[0][0]), -1, false);
        }
        
        # retorna o ultimo dia
        return $ultimoDia;
    }

######################################################################################################################

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

######################################################################################################################

    public function lotacaoCorreta($idServidor) {
        /*
         * Verifica se o Servidor cedido está na lotaçãop correta
         */

# Conecta com o banco de dados
        $servidor = new Pessoal();

        if ($servidor->get_idLotacao($idServidor) == 113) {
            return "Sim";
        } else {
            return '<span class=\'label alert\'>Não</span>';
        }
    }

######################################################################################################################
}
