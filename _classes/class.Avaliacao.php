<?php

class Avaliacao {

    /**
     * Abriga as várias rotina referentes a avaliacaço de desempenho de um servidor
     *
     * @author André Águia (Alat) - alataguia@gmail.com
     */
##############################################################

    public function getDados($id = null) {

        # Verifica se o id foi informado
        if (vazio($id)) {
            alert("É necessário informar o id.");
            return;
        }

        # Pega os dados
        $servidor = new Pessoal();
        $select = "SELECT *
                     FROM tbavaliacao
                    WHERE idAvaliacao = {$id}";

        # Retorno
        return $servidor->select($select, false);
    }

##############################################################

    public function exibe_tabelaRegras() {
        
        
        # Define o array
        $array = [
            ["AV1","8 meses"],
            ["AV2","16 meses"],
            ["AV3","24 meses"],
            ["AV4","36 meses"]            
        ];
        
        $tabela = new Tabela();
        $tabela->set_conteudo($array);
        $tabela->set_titulo("Avaliações");
        $tabela->set_label(["Avaliação","Período"]);
        $tabela->set_width([60, 40]);
        $tabela->set_align(["center", "center"]);
        $tabela->set_totalRegistro(false);
        $tabela->show();
    }

###########################################################

    function exibePublicacao($id) {

        /**
         * Informe os dados da Publicação
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = "SELECT dtPublicacao, pgPublicacao
                     FROM tbavaliacao
                    WHERE idAvaliacao = {$id}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        # Retorno
        if (empty($row[0])) {
            pLista("---");
        } else {
            pLista(
                    date_to_php($row[0]),
                    "pag: " . trataNulo($row[1])
            );
        }
    }

    ###########################################################

    function getPeriodoEAno($idServidor) {

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega a última avaliação desse servidor
        $select = "SELECT *
                     FROM tbavaliacao
                    WHERE idServidor = {$idServidor}
                 ORDER BY dtPeriodo1 DESC
                    LIMIT 1";

        $row = $pessoal->select($select, false);

        # Se estive vazio ele nunca teve avaliação o próximo é AV1
        if (empty($row['idAvaliacao'])) {
            $tipo = 1;  // tipo é estágio
            $dtPeriodo1 = $pessoal->get_dtAdmissao($idServidor);    // pega o início do período a data de admissão
            $dtPeriodo2 = addMeses($dtPeriodo1, 8);                 // o fim do período é 8 meses
            $dtPeriodo2 = addDias($dtPeriodo2, -1);                 // menos 1 dia
            $referencia = "AV1";
            return [$dtPeriodo1, $dtPeriodo2, $tipo, $referencia];
        } else {
            # Se o anterior foi AV1 o próximo é AV2
            if ($row['referencia'] == "AV1") {
                $tipo = 1;  // tipo é estágio
                $dtPeriodo1 = addDias(date_to_php($row['dtPeriodo2']), 2);
                $dtPeriodo2 = addMeses($dtPeriodo1, 8);
                $dtPeriodo2 = addDias($dtPeriodo2, -1);          // menos 1 dia
                $referencia = "AV2";
                return [$dtPeriodo1, $dtPeriodo2, $tipo, $referencia];
            }

            # Se o anterior foi AV2 o próximo é AV3
            if ($row['referencia'] == "AV2") {
                $tipo = 1;  // tipo é estágio
                $dtPeriodo1 = addDias(date_to_php($row['dtPeriodo2']), 2);
                $dtPeriodo2 = addMeses($dtPeriodo1, 8);
                $dtPeriodo2 = addDias($dtPeriodo2, -1);          // menos 1 dia
                $referencia = "AV3";
                return [$dtPeriodo1, $dtPeriodo2, $tipo, $referencia];
            }

            # Se o anterior foi AV3 o próximo é AV4
            # A ultima avaliação o período é de 12 meses (Segundo Débora)
            if ($row['referencia'] == "AV3") {
                $tipo = 1;  // tipo é estágio
                $dtPeriodo1 = addDias(date_to_php($row['dtPeriodo2']), 2);
                $dtPeriodo2 = addMeses($dtPeriodo1, 12);         // 12 meses
                $dtPeriodo2 = addDias($dtPeriodo2, -1);      // menos 1 dia
                $referencia = "AV4";
                return [$dtPeriodo1, $dtPeriodo2, $tipo, $referencia];
            }

            # Se o anterior foi AV4 o próximo é Anual            
            if ($row['referencia'] == "AV4") {
                $dtPeriodo1 = addDias(date_to_php($row['dtPeriodo2']), 2);

                # Verifica quanto falta para chegar o próximo 01/04
                $diaDt = day($dtPeriodo1);
                $anoDt = year($dtPeriodo1);
                $mesDt = month($dtPeriodo1);

                $tipo = 2;

                if ($mesDt < 4) {
                    $dtPeriodo2 = "31/03/{$anoDt}";
                    $referencia = $anoDt;
                } elseif ($mesDt == 4) {

                    if ($diaDt == 30) {
                        $dtPeriodo2 = "31/03/" . ($anoDt + 1);
                        $referencia = $anoDt + 1;
                    } else {
                        $dtPeriodo2 = ($diaDt + 1) . "/04/" . ($anoDt + 1);
                        $referencia = $anoDt + 1;
                    }
                } elseif ($mesDt > 4) {
                    $dtPeriodo2 = "31/03/" . ($anoDt + 1);
                    $referencia = $anoDt + 1;
                }

                return [$dtPeriodo1, $dtPeriodo2, $tipo, $referencia];
            }

            if ($row['tipo'] == 2) {
                $dtPeriodo1 = addDias(date_to_php($row['dtPeriodo2']), 2);
                $anoDt = year($dtPeriodo1);
                $tipo = 2;
                $referencia = $anoDt + 1;
                $dtPeriodo2 = "31/03/" . ($anoDt + 1);

                return [$dtPeriodo1, $dtPeriodo2, $tipo, $referencia];
            }
        }
    }

    ###########################################################

    function exibeNota1($id) {

        /**
         * Exibe a nota 1 de uma avaliação
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = "SELECT nota1
                     FROM tbavaliacao
                    WHERE idAvaliacao = {$id}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        $nota = $row[0];
        $porcentagem = (100 * $nota) / 120;

        # Retorno
        if (empty($nota)) {
            p("---", "pNota");
        } else {
            plista($nota, "(" . number_format($porcentagem, 1) . " %)");

//            p($nota, "pNota");
//
//            if ($porcentagem >= 90) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem1");
//            }
//
//            if ($porcentagem >= 70 AND $porcentagem < 90) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem2");
//            }
//
//            if ($porcentagem >= 50 AND $porcentagem < 70) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem3");
//            }
//
//            if ($porcentagem < 50) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem4");
//            }
        }
    }

    ###########################################################

    function exibeNota2($id) {

        /**
         * Exibe a nota 1 de uma avaliação
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = "SELECT nota2
                     FROM tbavaliacao
                    WHERE idAvaliacao = {$id}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        $nota = $row[0];
        $porcentagem = (100 * $nota) / 120;

        # Retorno
        if (empty($nota)) {
            p("---", "pNota");
        } else {
            plista($nota, "(" . number_format($porcentagem, 1) . " %)");

//            p($nota, "pNota");
//
//            if ($porcentagem >= 90) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem1");
//            }
//
//            if ($porcentagem >= 70 AND $porcentagem < 90) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem2");
//            }
//
//            if ($porcentagem >= 50 AND $porcentagem < 70) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem3");
//            }
//
//            if ($porcentagem < 50) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem4");
//            }
        }
    }

    ###########################################################

    function exibeNota3($id) {

        /**
         * Exibe a nota 1 de uma avaliação
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = "SELECT nota3
                     FROM tbavaliacao
                    WHERE idAvaliacao = {$id}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        $nota = $row[0];
        $porcentagem = (100 * $nota) / 120;

        # Retorno
        if (empty($nota)) {
            p("---", "pNota");
        } else {
            plista($nota, "(" . number_format($porcentagem, 1) . " %)");

//            p($nota, "pNota");
//
//            if ($porcentagem >= 90) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem1");
//            }
//
//            if ($porcentagem >= 70 AND $porcentagem < 90) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem2");
//            }
//
//            if ($porcentagem >= 50 AND $porcentagem < 70) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem3");
//            }
//
//            if ($porcentagem < 50) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem4");
//            }
        }
    }

    ###########################################################

    function exibeTotal($id) {

        /**
         * Exibe a nota 1 de uma avaliação
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = "SELECT nota1, nota2, nota3
                     FROM tbavaliacao
                    WHERE idAvaliacao = {$id}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        $nota = $row[0] + $row[1] + $row[2];
        $porcentagem = (100 * $nota) / 360;

        # Retorno
        if (empty($nota)) {
            p("---", "pNota");
        } else {
            plista($nota, "(" . number_format($porcentagem, 1) . " %)");

//            p($nota, "pNota");
//
//            if ($porcentagem >= 90) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem1");
//            }
//
//            if ($porcentagem >= 70 AND $porcentagem < 90) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem2");
//            }
//
//            if ($porcentagem >= 50 AND $porcentagem < 70) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem3");
//            }
//
//            if ($porcentagem < 50) {
//                p("(" . number_format($porcentagem, 1) . " %)", "pPercentagem4");
//            }
        }
    }

    ###########################################################

    function exibeResultado($id) {

        /**
         * Exibe a nota 1 de uma avaliação
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega os dias publicados
        $select = "SELECT nota1, nota2, nota3
                     FROM tbavaliacao
                    WHERE idAvaliacao = {$id}";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        $nota = $row[0] + $row[1] + $row[2];
        $porcentagem = (100 * $nota) / 360;

        # Retorno
        if (empty($nota)) {
            p("---", "pNota");
        } else {
            if ($porcentagem >= 90) {
                p("Habilitado para progressão diferenciada", "pPercentagem1");
            }

            if ($porcentagem >= 70 AND $porcentagem < 90) {
                p("Habilitado para progressão simples por merecimento:", "pPercentagem2");
            }

            if ($porcentagem >= 50 AND $porcentagem < 70) {
                p("Não definido na portaria", "pPercentagem3");
            }

            if ($porcentagem < 50) {
                p("Insuficiente", "pPercentagem4");
            }
        }
    }

    ###########################################################

    function getProcessoSei($idServidor) {

        # Verifica se o id foi informado
        if (vazio($idServidor)) {
            alert("É necessário informar o id.");
            return;
        }

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega a última avaliação desse servidor
        $select = "SELECT processoAvaliacaoSei	
                     FROM tbservidor
                    WHERE idServidor = {$idServidor}";

        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            return null;
        } else {
            return "SEI - {$row[0]}";
        }
    }

    ###########################################################

    function getProcessoFisico($idServidor) {

        # Verifica se o id foi informado
        if (vazio($idServidor)) {
            alert("É necessário informar o id.");
            return;
        }

        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega a última avaliação desse servidor
        $select = "SELECT processoAvaliacaoFisico	
                     FROM tbservidor
                    WHERE idServidor = {$idServidor}";

        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            return null;
        } else {
            return $row[0];
        }
    }

    ################################################################

    public function exibeProcesso($idServidor) {

        /**
         * Exibe os processos
         */
        # Verifica se o id foi informado
        if (vazio($idServidor)) {
            alert("É necessário informar o id.");
            return;
        } else {

            pLista(
                    $this->getProcessoSei($idServidor),
                    $this->getProcessoFisico($idServidor)
            );
        }
    }

###########################################################

    public function exibeObs($id) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        if (empty($id)) {
            echo "---";
        } else {

            # Pega array com os dias publicados
            $select = "SELECT obs
                     FROM tbavaliacao
                    WHERE idAvaliacao = {$id}";

            $retorno = $pessoal->select($select, false);
            if (empty($retorno[0])) {
                echo "---";
            } else {
                toolTip("Obs", $retorno[0]);
            }
        }
    }

###########################################################

    public function exibeAvaliacoes($idServidor) {

        /**
         * Exibe os processos
         */
        # Verifica se o id foi informado
        if (vazio($idServidor)) {
            alert("É necessário informar o id.");
            return;
        } else {
            # Conecta ao Banco de Dados
            $pessoal = new Pessoal();

            # Pega array com os dias publicados
            $select = "SELECT tipo,
                              referencia,
                              dtPeriodo1,
                              dtPeriodo2 
                         FROM tbavaliacao WHERE idServidor = {$idServidor}";

            $row = $pessoal->select($select);

            foreach ($row as $item) {
                # Tipo
                if ($row["tipo"] == 1) {
                    echo "Estágio - ";
                } else {
                    echo "Anual   - ";
                }

                # Referência
                echo $row["referencia"], " - ";

                # Período
                echo date_to_php($row["dtPeriodo1"]);
                echo " a ";
                echo date_to_php($row["dtPeriodo2"]);
            }
        }
    }

###########################################################

    public function exibeDataAdmissaoETempo($idServidor) {

        /**
         * Exibe um botao que exibirá a observação (quando houver)
         */
        # Conecta ao Banco de Dados
        $pessoal = new Pessoal();

        # Pega a data de admissão
        $dtAdmissao = $pessoal->get_dtAdmissao($idServidor);

        if (empty($idServidor)) {
            echo "---";
        } else {
            $tempo = intval(dataDif($dtAdmissao) / 30);

            p($dtAdmissao, "pprogressaoAdmissao");

            if ($tempo > 8) {
                p("({$tempo} meses)", "vermelho");
            } else {
                p("({$tempo} meses)", "verde");
            }
        }
    }

###########################################################
}
