<?php

class Cessao {

    /**
     * Abriga as várias rotina referentes a cessão de servidor da Uenf para outro órgão
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
##############################################################

    public function getDados($idHistCessao = null) {
        # Verifica se o id foi informado
        if (vazio($idHistCessao)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT *
                     FROM tbhistcessao
                    WHERE idHistCessao = {$idHistCessao}";

        # Retorno
        return $servidor->select($select, false);
    }

###########################################################

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
                          idHistCessao,
                          processo,
                          dtPublicacao,
                          idHistCessao
                     FROM tbhistcessao
                    WHERE idHistCessao = {$idHistCessao}";

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($servidor->select($select, true));
        $tabela->set_titulo("Dados da Cessão");
        $tabela->set_label(["Status", "Data Inicial", "Data Final", "Órgão Cessionário", "Contatos do Órgão", "Processo", "Data de Publicação", "Obs"]);
        $tabela->set_funcao([null, "date_to_php", "date_to_php", null, null, null, "date_to_php"]);
        $tabela->set_width([10, 10, 10, 15, 15, 15, 10, 5]);
        #$tabela->set_align(["center", "center", "center", "center", "center", "center", "left"]);
        $tabela->set_totalRegistro(false);
        $tabela->set_classe(["Cessao", null, null, null, "Cessao", null, null, "Cessao"]);
        $tabela->set_metodo(["getStatus", null, null, null, "exibeContatos", null, null, "exibeObs"]);

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

###########################################################

    public function getDataInicialFrequencia($idHistCessao) {
        /*
         * Exibe o primeiro dia do mês da frequencia de cessão que ainda não foi atestada
         * Para o sistema sugerir no formulário de cadastro de frequência
         */

        # Reserva uma variavel para a data escolhida
        $dataEscolhida = null;

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        # Pega os dados desta Cessão
        $dadosCessao = $this->getDados($idHistCessao);
        $idServidor = $dadosCessao["idServidor"];
        $dtInicial = date_to_php($dadosCessao["dtInicio"]);
        $dtFinal = date_to_php($dadosCessao["dtFim"]);

        # Pega os Dados das Frequencias cadastradas desta Cessao
        $select = "SELECT dtFinal, idServidor FROM tbfrequencia WHERE idHistCessao = {$idHistCessao} ORDER BY dtFinal DESC";
        $dadosFrequencia = $servidor->select($select, false);

        # Verifica as frequencias
        if (empty($dadosFrequencia)) {
            # Nao tem nenhuma frequência pega a data inicial da cessao
            $dataEscolhida = $dtInicial;
        } else {
            # Se tiver frequência pega a data posterior a última frequência cadastrada
            $dataEscolhida = addDias(date_to_php($dadosFrequencia[0]), 1, false);
        }

        # Pega o ultimo dia do mês
        $ultimoDia = ultimoDiaMes($dataEscolhida);

        # Quando tiver data final...
        if (!empty($dtFinal)) {

            # Verifica se a data escolhida é posterior ao término da cessão
            if ($dataEscolhida == dataMaior($dataEscolhida, $dtFinal)) {
                # Retorna nulo pois não tem mais datas disponíveis
                return null;
            }

            # Verifica se o ultimo dia do mês é posterior ao ultimo dia da cessão
            if ($ultimoDia == dataMaior($dtFinal, $ultimoDia)) {
                # Se for o último dia do mês passa a ser o último dia da cessão
                $ultimoDia = $dtFinal;
            }
        }

        # Verifica se tem afastamento o período entre a data escolhida e o último dia do mês
        $verificaDados = new VerificaDadosAfastamento($idServidor, $dataEscolhida, $ultimoDia);
        $verifica = $verificaDados->verifica();

        # Se tiver afastamento Temos que avaliar as datas deste afastamento
        if (!empty($verifica)) {

            # Pega as datas do afastamento
            $dtInicialAfast = date_to_php($verifica["dtInicial"]);
            $dtFinalAfast = date_to_php($verifica["dtFinal"]);

            # Verifica se a data escolhida está dentro do afastamanto
            if ($dataEscolhida == $dtInicialAfast OR $dataEscolhida == dataMaior($dataEscolhida, $dtInicialAfast)) {
                # Então o primeiro dia é o dia após este afastamnento
                $dataEscolhida = addDias($dtFinalAfast, 1, false);
            }
        }

        # Pega o ultimo dia do mês
        $ultimoDia = ultimoDiaMes($dataEscolhida);

        # Segunda Verificação
        # Verifica se tem afastamento o período entre a data escolhida e o último dia do mês
        $verificaDados = new VerificaDadosAfastamento($idServidor, $dataEscolhida, $ultimoDia);
        $verifica = $verificaDados->verifica();

        # Se tiver afastamento Temos que avaliar as datas deste afastamento
        if (!empty($verifica)) {
            # Pega as datas do afastamento
            $dtInicialAfast = date_to_php($verifica["dtInicial"]);
            $dtFinalAfast = date_to_php($verifica["dtFinal"]);

            # Verifica se a data escolhida está dentro do afastamanto
            if ($dataEscolhida == $dtInicialAfast OR $dataEscolhida == dataMaior($dataEscolhida, $dtInicialAfast)) {
                # Então o primeiro dia é o dia após este afastamnento
                $dataEscolhida = addDias($dtFinalAfast, 1, false);
            }
        }

        # Verifica se a data escolhida posterior a hoje
        if (dataMaior($dataEscolhida, date("d/m/Y")) == $dataEscolhida) {
            return null;
        } else {
            return $dataEscolhida;
        }
    }

###########################################################

    public function getDataFinalFrequencia($idHistCessao) {
        /*
         * Exibe o ultimo dia do mês da frequencia de cessão que ainda não foi atestada
         * Para o sistema sugerir no formulário de cadastro de frequência
         */

        # Pega os dados desta Cessão
        $dadosCessao = $this->getDados($idHistCessao);
        $idServidor = $dadosCessao["idServidor"];
        $dtInicial = date_to_php($dadosCessao["dtInicio"]);
        $dtFinal = date_to_php($dadosCessao["dtFim"]);

        # Pega o primeiro dia disponível
        $primeiroDia = $this->getDataInicialFrequencia($idHistCessao);

        # Verifica se o primeiro dia é alcançável. Se não for o ultimo tb não será e retorna nulo
        if (!empty($primeiroDia)) {

            # Pega o último dia do mês
            $ultimoDia = ultimoDiaMes($primeiroDia);

            # Verifica se é esse o mês do término
            if (!empty($dtFinal) AND month($primeiroDia) == month($dtFinal) AND year($primeiroDia) == year($dtFinal)) {
                # Se for o ultimo dia do mês é o último dia da cessão
                $ultimoDia = $dtFinal;
            }

            # Verifica se tem afastamento o período entre a data escolhida e o último dia do mês
            $verificaDados = new VerificaDadosAfastamento($idServidor, $primeiroDia, $ultimoDia);
            $verifica = $verificaDados->verifica();

            # Se tiver afastamento Temos que avaliar as datas deste afastamento
            if (!empty($verifica)) {
                # Pega as datas do afastamento
                $dtInicialAfast = date_to_php($verifica["dtInicial"]);
                $dtFinalAfast = date_to_php($verifica["dtFinal"]);
                $ultimoDia = addDias($dtInicialAfast, -1, false);
            }

            # retorna o ultimo dia
            return $ultimoDia;
        } else {
            return null;
        }
    }

###########################################################

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

###########################################################

    public function lotacaoCorreta($idServidor) {
        /*
         * Verifica se o Servidor cedido está na lotação correta
         */

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        if ($servidor->get_idLotacao($idServidor) == 113) {
            return "Sim";
        } else {
            return '<span class=\'label alert\'>Não</span>';
        }
    }

###########################################################

    public function getNumCessaoVigente($idServidor) {
        /*
         * Retorna o número de cessões vigentes
         * Objetivo é encontrar problemas de servidor com mais de 1 cessão vigente
         */

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $select = "SELECT idHistCessao
                     FROM tbhistcessao
                    WHERE idServidor = {$idServidor}
                     AND (tbhistcessao.dtFim IS NULL OR (now() BETWEEN tbhistcessao.dtInicio AND tbhistcessao.dtFim))";

        return $servidor->count($select);
    }

###########################################################

    public function getidCessaoVigente($idServidor) {
        /*
         * Retorna o número de cessões vigentes
         * Objetivo é encontrar problemas de servidor com mais de 1 cessão vigente
         */

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $select = "SELECT idHistCessao
                     FROM tbhistcessao
                    WHERE idServidor = {$idServidor}
                     AND (tbhistcessao.dtFim IS NULL OR (now() BETWEEN tbhistcessao.dtInicio AND tbhistcessao.dtFim))
                   ORDER BY dtInicio desc 
                   LIMIT 1";

        $row = $servidor->select($select, false);
        return $row[0];
    }

###########################################################

    public function getNumLancamentosFrequencia($idHistCessao) {
        /*
         * Retorna o número de lançamento de frequencia nesta cessão
         */

        # Conecta com o banco de dados
        $servidor = new Pessoal();

        $select = "SELECT idFrequencia
                     FROM tbfrequencia
                    WHERE idHistCessao = {$idHistCessao}";

        $num = $servidor->count($select);

        # Botão de controle de frequência
        $botao = new BotaoGrafico();
        $botao->set_title("{$num} Lançamentos de Frequência");
        $botao->set_label($num);
        $botao->set_url("servidorFrequencia.php?idHistCessao={$idHistCessao}");
        $botao->set_imagem(PASTA_FIGURAS . 'frequencia.jpg', 23, 23);
        $botao->show();
    }

###########################################################

    public function exibeContatos($id) {

        # Verifica se o id foi informado
        if (vazio($id)) {
            alert("É necessário informar o id.");
            return;
        } else {
            # Conecta com o banco de dados
            $servidor = new Pessoal();

            $select = "SELECT orgaoTel, 
                              orgaoEmail
                         FROM tbhistcessao
                        WHERE idHistCessao = {$id}";

            $row = $servidor->select($select, false);

            if (!empty($row["orgaoTel"])) {
                echo "Tel: " . $row["orgaoTel"];
            }

            if (!empty($row["orgaoEmail"])) {
                echo "Email: " . $row["orgaoEmail"];
            }
        }
    }

###########################################################

    public function exibeObs($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = "SELECT obs
                     FROM tbhistcessao
                    WHERE idHistCessao = {$id}";

        $retorno = $pessoal->select($select, false);

        if (empty($retorno["obs"])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno["obs"]);
        }
    }

###########################################################

    public function getOrgaoDtInicial($idServidor = null, $dtInicial = null) {

        /**
         * Informa o nome do órgão em que este servidor iniciou cessão na data informada
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Trata o $idServidor
        if (empty($idServidor)) {
            return null;
        }

        # Trata a data de início
        if (empty($dtInicial)) {
            return null;
        } else {
            $dtInicial = date_to_bd($dtInicial);
        }

        # Pega array com os dias publicados
        $select = "SELECT orgao
                     FROM tbhistcessao
                    WHERE idServidor = {$idServidor} 
                      AND dtInicio = '{$dtInicial}'";

        $retorno = $pessoal->select($select, false);
        if (empty($retorno["orgao"])) {
            return "---";
        } else {
            return $retorno["orgao"];
        }
    }

###########################################################

    public function getOrgaoDtFinal($idServidor = null, $dtFinal = null) {

        /**
         * Informa o nome do órgão em que este servidor iniciou cessão na data informada
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Trata o $idServidor
        if (empty($idServidor)) {
            return null;
        }

        # Trata a data de início
        if (empty($dtFinal)) {
            return null;
        } else {
            $dtFinal = date_to_bd($dtFinal);
        }

        # Pega array com os dias publicados
        $select = "SELECT orgao
                     FROM tbhistcessao
                    WHERE idServidor = {$idServidor} 
                      AND dtFim = '{$dtFinal}'";

        $retorno = $pessoal->select($select, false);
        if (empty($retorno["orgao"])) {
            return "---";
        } else {
            return $retorno["orgao"];
        }
    }

###########################################################
}
