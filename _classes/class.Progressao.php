<?php

class Progressao {

    /**
     * Abriga as várias rotina do Cadastro de progressão de um servidor
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    private $idServidor = null;

    ###########################################################

    /**
     * Método Construtor
     */
    public function __construct($idServidor = null) {

        $this->idServidor = $idServidor;
    }

    ###########################################################

    function get_dados($idProgressao) {

        /**
         * Fornece os todos os dados de um idProgressao
         */
        # Pega os dados
        $select = "SELECT *
                   FROM tbprogressao
                  WHERE idProgressao = $idProgressao";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados;
    }

    ###########################################################

    function get_idClasseAtual($idServidor = null) {

        /**
         * Fornece o idClasse atual do servidor
         */
        # Troca o valor informado para a variável da classe
        if (!vazio($idServidor)) {
            $this->idServidor = $idServidor;
        }

        $select = "SELECT idClasse
                     FROM tbprogressao
                    WHERE idServidor = $this->idServidor
                 ORDER BY dtInicial desc";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        return $row[0];
    }

    ###########################################################

    function get_idPlanoAtual($idServidor = null) {

        /**
         * Fornece o idPlano atual do servidor
         */
        # Troca o valor informado para a variável da classe
        if (!vazio($idServidor)) {
            $this->idServidor = $idServidor;
        }

        $select = "SELECT tbclasse.idPlano
                     FROM tbprogressao LEFT JOIN tbclasse USING (idCLasse)
                    WHERE idServidor = $this->idServidor
                 ORDER BY dtInicial desc";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        return $row[0];
    }

    ###########################################################

    function get_FaixaAtual($idServidor = null) {

        /**
         * Fornece o Nivel/Faixa/Padrao do servidor
         */
        # Troca o valor informado para a variável da classe
        if (!vazio($idServidor)) {
            $this->idServidor = $idServidor;
        }

        $select = "SELECT tbclasse.faixa
                     FROM tbprogressao LEFT JOIN tbclasse USING (idCLasse)
                    WHERE idServidor = $this->idServidor
                 ORDER BY dtInicial desc";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        return $row[0];
    }

    ###########################################################

    function get_dtInicialAtual($idServidor) {

        /**
         * Fornece a data Inicial da progressão atual do servidor
         */
        # Troca o valor informado para a variável da classe
        if (!vazio($idServidor)) {
            $this->idServidor = $idServidor;
        }

        $select = "SELECT dtInicial
                     FROM tbprogressao
                    WHERE idServidor = $this->idServidor
                 ORDER BY dtInicial desc";

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        return date_to_php($row[0]);
    }

    ###########################################################

    function analisaServidor($idServidor) {

        /**
         * Fornece a data Inicial da progressão atual do servidor
         */
        # Troca o valor informado para a variável da classe
        if (!vazio($idServidor)) {
            $this->idServidor = $idServidor;
        }

        # Acessa os bancos
        $pessoal = new Pessoal();
        $plano = new PlanoCargos();

        ########################
        # Pega os dados do servidor
        # Pega o salário (idClasse) atual do servidor
        $idClasse = $this->get_idClasseAtual($idServidor);

        # Pega o plano de cargos (idPlano) atual do servidor
        $idPlano = $this->get_idPlanoAtual($idServidor);

        # Pega o cargo (idCargo) do servidor        
        $idCargo = $pessoal->get_idCargo($this->idServidor);

        ########################
        # Pega os dados da tabela
        # Pega o idCLasse da última classe possível do plano de cargos vigente para esse cargo        
        $idClasseUltimo = $plano->get_ultimoIdClasse($idCargo);

        # Pega o plano de cargos atual
        $idPlanoAtual = $plano->get_planoAtual();

        ########################
        # Verifica se tem algum salário cadastrado
        if (is_null($idClasse)) {
            $analise = "Não Tem Salário Cadastrado";
        } else {
            # Analisa se o servidor está na última classe possível
            if ($idClasse == $idClasseUltimo) {
                $analise = "Não Pode Progredir";
            } else {
                if ($idPlano <> $idPlanoAtual) {
                    $analise = "Plano ERRADO";
                } else {
                    # Pega a última progressão válida
                    $ultimaProgressao = new DateTime($this->get_ultimaProgressaoServidorVálida($idServidor));

                    # Pega a data de hoje
                    $hoje = new DateTime();

                    # Calcula o intervalo
                    $intervalo = $ultimaProgressao->diff($hoje);

                    # Verifica se já tem 4 anos ou mais
                    if ($intervalo->y >= 4) {
                        $analise = "Tem Direito a Progressão por Antiguidade";
                    } else {
                        $analise = "Aparentemente Tudo Certo";
                    }
                }
            }
        }
        return $analise;
    }

    ###########################################################

    function get_ultimaProgressaoServidorVálida($idServidor) {

        /**
         * Fornece a última progressão válida de um servidor para efeito de contagem de tempo para progressão por antiguidade
         */
        # Pega os dados
        $select = "SELECT dtInicial
                   FROM tbprogressao
                  WHERE idServidor = $idServidor 
                    AND (idTpProgressao  = 1 OR idTpProgressao  = 2 OR idTpProgressao  = 3 OR idTpProgressao  = 4 OR idTpProgressao  = 6)
                  ORDER BY dtInicial DESC";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        return $dados[0];
    }

    ###########################################################

    function exibeDadosSalarioAtual($idServidor) {

        /**
         * Exibe vários dados referente ao salário atual do servidor
         */
        # Verifica se o id foi enviado
        if (empty($idServidor)) {
            return null;
        } else {
            # Pega o idClasse atual 
            $idClasse = $this->get_idClasseAtual($idServidor);

            # Pega os dados desta idClasse
            $classe = new Classe();
            $dados = $classe->get_dados($idClasse);
            if (empty($dados[0])) {
                return null;
            } else {

                # Exibe os dados
                pLista(
                        "Faixa: " . $dados["faixa"],
                        "Valor: $" . $dados["valor"],
                        "Plano: " . $dados["idPlano"],
                        "Nível: " . $dados["nivel"]
                );
            }
        }
    }

###########################################################

    function exibeDadosSalarioNovo($idServidor) {

        /**
         * Exibe vários dados referente ao salário Novo do servidor 
         * (Aumento de salário de janeiro dse 2022)
         */
        # Define o novo plano
        $idPlano = 14;

        # Verifica se o id foi enviado
        if (empty($idServidor)) {
            return null;
        } else {
            # Pega o idClasse atual 
            $idClasse = $this->get_idClasseAtual($idServidor);

            if (empty($idClasse)) {
                return null;
            } else {

                # Pega a faixa. que servirá de base para a importação
                $classe = new Classe();
                $dados = $classe->get_dados($idClasse);
                $faixa = $dados["faixa"];

                if ($dados["idPlano"] == 8) {

                    # Pega essa faixa na nova tabela
                    $select = "SELECT idClasse 
                         FROM tbclasse
                        WHERE faixa = '{$faixa}'
                          AND idPlano = 14";

                    $pessoal = new Pessoal();
                    $row = $pessoal->select($select, false);

                    if (empty($row[0])) {
                        return null;
                    } else {
                        # Pega os dados dessa faixa na nova tabela
                        $dados2 = $classe->get_dados($row[0]);

                        # Exibe os dados
                        pLista(
                                "Faixa: " . $dados2["faixa"],
                                "Valor: $" . $dados2["valor"],
                                "Plano: " . $dados2["idPlano"],
                                "Nível: " . $dados2["nivel"]
                        );
                    }
                } else {
                    return null;
                }
            }
        }
    }

    ###########################################################

    function exibeLancamento($idServidor) {

        /**
         * Exibe e faz os lançamentos 
         * (Aumento de salário de janeiro dse 2022)
         */
        # Define o novo plano
        $idPlano = 14;

        # Verifica se o id foi enviado
        if (empty($idServidor)) {
            return null;
        } else {
            # Pega o idClasse atual 
            $idClasse = $this->get_idClasseAtual($idServidor);

            if (empty($idClasse)) {
                return null;
            } else {

                # Pega a faixa. que servirá de base para a importação
                $classe = new Classe();
                $dados = $classe->get_dados($idClasse);
                $faixa = $dados["faixa"];

                if ($dados["idPlano"] == 8) {

                    # Pega essa faixa na nova tabela
                    $select = "SELECT idClasse 
                             FROM tbclasse
                            WHERE faixa = '{$faixa}'
                              AND idPlano = 14";

                    $pessoal = new Pessoal();
                    $row = $pessoal->select($select, false);

                    if (empty($row[0])) {
                        return null;
                    } else {
                        # Pega os dados dessa faixa na nova tabela
                        $dados2 = $classe->get_dados($row[0]);
                        echo "idServidor: {$idServidor}";
                        br();
                        echo "idTpProgressao: 5";
                        br();
                        echo "idClasse: {$row[0]}";
                        br();
                        echo "dtPublicacao: 28/01/2022";
                        br();
                        echo "dtInicial: 01/01/2022";
                        br();
                        echo "obs: De acordo com a Lei Estadual nº 9.436, de 14 de outubro de 2021";
                        br();
                        #echo "INSERT INTO tbprogressao ('idServidor','idTpProgressao','idClasse','dtPublicacao','dtInicial','obs') VALUES ({$idServidor},5,'2022_01_28','2022_01_01','De acordo com a Lei Estadual nº 9.436, de 14 de outubro de 2021'";
                        # Grava na tabela
                        $campos = ['idServidor', 'idTpProgressao', 'idClasse', 'dtPublicacao', 'dtInicial', 'obs'];
                        $valor = [$idServidor, 5, $row[0], '2022_01_28', '2022_01_01', 'De acordo com a Lei Estadual nº 9.436, de 14 de outubro de 2021'];
                        $pessoal->gravar($campos, $valor, null, "tbprogressao", "idProgressao", false);
                    }
                } else {
                    return null;
                }
            }
        }
    }

    ###########################################################

    function get_planoVigenteNaEpocaServidor($data, $idServidor = null) {

        /**
         * Fornece o idPlano do plano vigente do servidor na data informada
         */
        # Pega os dados do servidor
        $pessoal = new Pessoal();

        # Se é professor ou adm/tec
        $idCargo = $pessoal->get_idCargo($idServidor);

        # Acerta a data
        $data = date_to_bd($data);

        # Trata o cargo
        if ($idCargo == 128 OR $idCargo == 129) {
            $tipoCargo = "Professor";
        } else {
            $tipoCargo = "Adm/Tec";
        }

        # Se é ex-Fenorte
        ##########################################Parei aqui
        # Pega os dados
        $select = "SELECT idPlano
                     FROM tbplano
                    WHERE dtVigencia <= '{$data}'";

        if ($tipoCargo == "Professor") {
            $select .= " AND (servidores = 'Todos' OR servidores = 'Professor')";
        }

        if ($tipoCargo == "Adm/Tec") {
            $select .= " AND (servidores = 'Todos' OR servidores = 'Adm/Tec')";
        }

        $select .= " ORDER BY dtVigencia DESC LIMIT 1";

        $pessoal = new Pessoal();
        $dados = $pessoal->select($select, false);

        # Existem 2 planos de cargos com a mesma data de Vigência
        # 6 - Plano para servidores da FENORTE
        # 9 - Para os Servidores da UENF
        # A rotina abaixo acerta caso o servidor for ex-Fenorte passa para 6 senão passa para 9        
        if ($dados[0] == 6) {
            if ($pessoal->exFenorte($idServidor)) {
                return 6;
            } else {
                return 9;
            }
        }

        # Verifica se é o plano Lei nº 5759 / 2010 (id 10) que só valeu para a Uenf
        if ($dados[0] == 10) {
            if ($pessoal->exFenorte($idServidor)) {
                return 6;
            } else {
                return 10;
            }
        }

        return $dados[0];
    }

    ###########################################################

    function get_planoSugerido($idProgressao = null) {

        /**
         * Fornece o idPlano sugerido
         */
        # Trata o id
        if (empty($idProgressao)) {
            return null;
        } else {
            # Inicia a classe PlanoCargos
            $plano = new PlanoCargos();

            # Pega os dados dessa progressao
            $dados = $this->get_dados($idProgressao);

            return $plano->get_numDecreto($this->get_planoVigenteNaEpocaServidor(date_to_php($dados['dtInicial']), $dados['idServidor']));
        }
    }

    ###########################################################

    function verificaProblemaPlano($idProgressao = null, $exibeTabela = true) {

        /**
         * Verifica se tem problema
         */
        # Trata o id
        if (empty($idProgressao)) {
            return null;
        } else {
            # Inicia a classe PlanoCargos
            $plano = new PlanoCargos();

            # Pega o Plano Cadastrado            
            $planoCadastrado = $plano->get_numDecreto($this->get_idPlano($idProgressao));

            # Pega o idPlano Sugerido
            $planoSugerido = $this->get_planoSugerido($idProgressao);

            # Verifica se são iguais
            if ($planoCadastrado <> $planoSugerido) {

                # Pegaa data inicial
                $dados = $this->get_dados($idProgressao);
                $dtinicial = date_to_php($dados['dtInicial']);

                if ($exibeTabela) {
                    echo "<span id='vermelho'>SIM</span>";
                    echo "<span id='f10'><br/>Este plano não estava<br/>vigente nesta data</span>";
                } else {
                    $painel = new Callout("warning");
                    $painel->abre();
                    p("<b>Atenção Problema Encontrado:</b><br/>O Plano de Cargos do <b>{$planoCadastrado}</b> não estava vigente em {$dtinicial}.<br/> O plano sugerido é: <b>{$planoSugerido}</b>", "f16", "center");
                    $painel->fecha();
                }
            } else {
                if ($exibeTabela) {
                    echo "<span id='verde'>NÃO</span>";
                }
            }
        }
    }

    ###########################################################

    function get_idPlano($idProgressao = null) {

        /**
         * Fornece o idPlano de uma progressão
         */
        # Troca o valor informado para a variável da classe
        if (empty($idProgressao)) {
            return null;
        } else {

            $select = "SELECT tbclasse.idPlano
                         FROM tbprogressao LEFT JOIN tbclasse USING (idCLasse)
                        WHERE idProgressao = {$idProgressao}";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);
            return $row[0];
        }
    }

    ###########################################################
}
