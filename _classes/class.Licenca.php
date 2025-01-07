<?php

class Licenca {

    /**
     * Abriga as várias rotina referentes a cessão de servidor da Uenf para outro órgão
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
##############################################################

    public function get_dados($id = null) {

        /**
         * Informa os dados da base de dados
         * 
         * @param $id integer null O id 
         * 
         * @syntax $licenca->get_dados([$id]);
         */
        # Joga o valor informado para a variável da classe
        if (!empty($id)) {
            $pessoal = new Pessoal();
            return $pessoal->select("SELECT * FROM tblicenca WHERE idLicenca = {$id}", false);
        } else {
            return null;
        }
    }

    ###########################################################

    public function exibeNome($idTpLicenca = null) {
        # Verifica se o id foi informado
        if (empty($idTpLicenca)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT nome, lei
                     FROM tbtipolicenca
                    WHERE idTpLicenca = {$idTpLicenca}";

        $licenca = $servidor->select($select, false);
        pLista(
                $licenca[0],
                $licenca[1]
        );
    }

##############################################################

    public function getNome($idTpLicenca = null) {
        # Verifica se o id foi informado
        if (empty($idTpLicenca)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT nome
                     FROM tbtipolicenca
                    WHERE idTpLicenca = {$idTpLicenca}";

        $licenca = $servidor->select($select, false);
        return $licenca[0];
    }

##############################################################

    public function exibeNomeSimples($idTpLicenca = null) {
        # Verifica se o id foi informado
        if (vazio($idTpLicenca)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT nome
                     FROM tbtipolicenca
                    WHERE idTpLicenca = {$idTpLicenca}";

        $licenca = $servidor->select($select, false);
        return $licenca[0];
    }

##########################################################################################

    public function exibeObs($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega array com os dias publicados
        $select = 'SELECT obs
                     FROM tblicenca
                    WHERE idLicenca = ' . $id;

        $retorno = $pessoal->select($select, false);
        if (empty($retorno[0])) {
            echo "---";
        } else {
            toolTip("Obs", $retorno[0]);
        }
    }

##########################################################################################

    public function exibePeriodo($idLicenca = null) {

        # Verifica se o id foi informado
        if (empty($idLicenca)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT dtInicial,
                          numdias,
                          ADDDATE(dtInicial,numDias-1) as dtTermino
                     FROM tblicenca
                    WHERE idLicenca = {$idLicenca}";

        $licenca = $servidor->select($select, false);

        echo "Início: ", date_to_php($licenca['dtInicial']);
        br();
        echo "Período: ", $licenca['numdias'], " dias";
        br();
        echo "Término: ", date_to_php($licenca['dtTermino']);
    }

##########################################################################################

    public function analisaTermino($idLicenca = null) {

        # Verifica se o id foi informado
        if (empty($idLicenca)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT alta,
                          TIMESTAMPDIFF(DAY,CURDATE(),ADDDATE(dtInicial,numDias-1)) as faltam
                     FROM tblicenca
                    WHERE idLicenca = {$idLicenca}";

        $licenca = $servidor->select($select, false);

        # Verifica se tem ou não ALTA
        if ($licenca['alta'] == 1) {
            if ($licenca['faltam'] > 0) {
                echo "Falta(m) " . abs($licenca['faltam']) . " dias<br/>para terminar COM ALTA";
            } else {
                echo "Terminado COM ALTA<br/>há " . abs($licenca['faltam']) . " dias";
            }
        } else {
            if ($licenca['faltam'] > 0) {
                echo "Falta(m) " . abs($licenca['faltam']) . " dias<br/>para terminar<br/>";
                label("SEM ALTA");
            } else {
                label("Licença em aberto");
                echo "<br>há " . abs($licenca['faltam']) . " dias";
            }
        }
    }

##########################################################################################

    public function exibePublicacao($idLicenca = null) {

        # Verifica se o id foi informado
        if (empty($idLicenca)) {
            return "---";
        } else {
            # Pega os dados
            $servidor = new Pessoal();
            $select = "SELECT dtPublicacao,
                              pgPublicacao
                         FROM tblicenca
                        WHERE idLicenca = {$idLicenca}";

            $row = $servidor->select($select, false);

            # trata a página
            if (!empty($row["pgPublicacao"])) {
                $row["pgPublicacao"] = "pag: " . $row['pgPublicacao'];
            }

            # Exibe a publicação
            plista(
                    date_to_php($row['dtPublicacao']),
                    $row['pgPublicacao']
            );
        }
    }

##########################################################################################

    public function get_processo($idLicenca = null) {

        # Verifica se o id foi informado
        if (empty($idLicenca)) {
            return null;
        } else {
            # Pega os dados
            $servidor = new Pessoal();
            $select = "SELECT processo
                         FROM tblicenca
                        WHERE idLicenca = {$idLicenca}";

            $row = $servidor->select($select, false);
            return $row[0];
        }
    }

##########################################################################################

    public function get_dtPublicacao($idLicenca = null) {

        # Verifica se o id foi informado
        if (empty($idLicenca)) {
            return null;
        } else {
            # Pega os dados
            $servidor = new Pessoal();
            $select = "SELECT dtPublicacao
                         FROM tblicenca
                        WHERE idLicenca = {$idLicenca}";

            $row = $servidor->select($select, false);
            return $row[0];
        }
    }

##############################################################

    public function exibeDoc($id = null) {
        # Verifica se o id foi informado
        if (empty($id)) {
            return "---";
        } else {
            # Pega o tipo de licença
            $pessoal = new Pessoal();
            $tipo = $pessoal->get_tipoLicenca($id);

            if ($pessoal->get_licencaPericia($tipo) == "Sim") {
                $medica = new LicencaMedica();
                $medica->exibeBim($id);
            } else {

                switch ($tipo) {
                    case 25: // Faltas
                        $faltas = new Faltas();
                        $faltas->exibeDoc($id);
                        break;

                    case 26: // Suspensão
                        $suspensao = new Suspensao();
                        $suspensao->exibePublicacaoPdf($id);
                        break;

                    case 3: // Declaração
                        $this->exibeDeclaracaoLicencaGestante($id);
                        break;

                    case 11: // Casamento
                    case 12: // Nojo
                        $this->exibeDocumentoAfastamentoPdf($id);
                        break;

                    default:
                        echo "---";
                        break;
                }
            }
        }
    }

    ###########################################################

    public function exibeDeclaracaoLicencaGestante($idLicenca = null) {
        # Verifica se o id foi informado
        if (empty($idLicenca)) {
            return "---";
        } else {
            $botao = new BotaoGrafico();
            $botao->set_label("Declaração");
            $botao->set_url("../grhRelatorios/declaracao.LicencaMaternidade.php?id={$idLicenca}");
            $botao->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
            $botao->set_title("Exibe a Declaração do período de licença maternidade. Esta declaração é necessário para solicitar a licença aleitamento");
            $botao->set_target("_blank");
            $botao->show();
        }
    }

    ###########################################################

    public function exibeDocumentoAfastamentoPdf($idLicenca = null) {
        # Verifica se o id foi informado
        if (empty($idLicenca)) {
            return "---";
        } else {
            # Monta o arquivo
            $arquivo = PASTA_AFASTAMENTOS . "{$idLicenca}.pdf";

            # Verifica se ele existe
            if (file_exists($arquivo)) {

                $botao = new BotaoGrafico();
                $botao->set_url($arquivo);
                $botao->set_imagem(PASTA_FIGURAS . 'doc.png', 20, 20);
                $botao->set_title("Exibe o documento arquivado");
                $botao->set_target("_blank");
                $botao->show();
            } else {
                return "---";
            }
        }
    }

    ########################################################### 
}
