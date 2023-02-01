<?php

class PlanoCargos {

    /**
     * Abriga as várias rotina do Cadastro de Planos de cargos e Tasbelas salariais
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $projeto        integer null O id do projeto a ser acessado
     * 
     */
    private $idPlano = null;

    ###########################################################

    /**
     * Método Construtor
     */
    public function __construct() {
        
    }

    ###########################################################

    public function get_dadosPlano($idPlano = null) {
        /**
         * Retorna um array com todas as informações do plano de cargos informado
         * 
         * @param $idPlano integer null o $idPlano
         * 
         * @syntax $plano->get_dadosPlano([$idPlano]);  
         */
        # Pega os planos cadastrados
        $select = 'SELECT numDecreto,
                          dtPublicacao,
                          dtDecreto,
                          planoAtual,
                          link,
                          dtVigencia,
                          servidores
                     FROM tbplano
                     WHERE idPlano = ' . $idPlano;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            return null;
        } else {
            return $row;
        }
    }

    ###########################################################

    public function get_numDecreto($idPlano = null) {
        /**
         * Retorna o numero do decreto / lei
         * 
         * @param $idPlano integer null o $idPlano
         * 
         */
        # Pega os planos cadastrados
        $select = 'SELECT numDecreto
                     FROM tbplano
                     WHERE idPlano = ' . $idPlano;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        return $row[0];
    }

    ###########################################################

    public function get_numDadosPlano($idPlano = null) {
        /**
         * Retorna o número de lançamentos de um plano
         * 
         * @param $idPlano integer null o $idPlano
         * 
         * @syntax $plano->get_dadosPlano([$idPlano]);  
         */
        # Pega os planos cadastrados
        $select = 'SELECT idClasse
                     FROM tbclasse
                     WHERE idPlano = ' . $idPlano;

        $pessoal = new Pessoal();
        return $pessoal->count($select);
    }

    ###########################################################

    public function exibeDadosPlano($idPlano = null, $relatorio = false) {
        /**
         * Exibe uma pequena tabela com o dados do plano
         * 
         * @param $idPlano integer null o $idPlano
         * 
         * @syntax $plano->exibeDAdosPlano([$idPlano]);  
         */
        # Conecta
        $pessoal = new Pessoal();

        # Pega o nome da tabela
        $dados = $this->get_dadosPlano($idPlano);

        # Verifica o status
        if ($dados['planoAtual']) {
            $status = "Vigente";
        } else {
            $status = "Antigo";
        }

        if ($relatorio) {
            $relatorio = new Relatorio('relatorioFichaCadastral');
            $relatorio->set_titulo($dados[0]);
            $relatorio->set_label(["Data da Lei/Decreto", "Data da Publicação", "Data do Início da Vigência", "Servidores", "Status"]);
            $relatorio->set_width([15, 15, 15, 10, 5]);
            $relatorio->set_conteudo(array([date_to_php($dados[2]), date_to_php($dados[1]), date_to_php($dados[5]), $dados[6], $status]));
            $relatorio->set_subTotal(false);
            $relatorio->set_totalRegistro(false);
            $relatorio->set_dataImpressao(false);
            $relatorio->set_linhaNomeColuna(false);
            $relatorio->set_brHr(0);
            $relatorio->set_logDetalhe("Visualizou a tabela de salário");
            $relatorio->show();
        } else {
            # Exibe a tabela identificando o plano
            $tabela = new Tabela();
            $tabela->set_titulo($dados[0]);
            $tabela->set_conteudo(array([date_to_php($dados[2]), date_to_php($dados[1]), date_to_php($dados[5]), $dados[6], $status]));
            $tabela->set_label(["Data da Lei/Decreto", "Data da Publicação", "Data do Início da Vigência", "Servidores", "Status"]);
            $tabela->set_totalRegistro(false);
            $tabela->set_width([15, 15, 15, 10, 5]);

            $formatacaoCondicional = array(
                array('coluna' => 3,
                    'valor' => $dados[6],
                    'operador' => '=',
                    'id' => 'listaDados'));

            $tabela->set_formatacaoCondicional($formatacaoCondicional);
            $tabela->show();
        }
    }

    ###########################################################

    public function exibeTabela($idPlano = null, $relatorio = false) {
        /**
         * Retorna a tabela salarial do plano
         * 
         * @param $idPlano integer null o $idPlano
         * 
         * @syntax $plano->exibeTabela([$idPlano]);  
         */
        # Conecta
        $pessoal = new Pessoal();

        # Define a ordem dos niveis
        $nivel = array("Elementar", "Fundamental", "Médio", "Superior", "Doutorado");

        # Define as variáveis secundárias
        $faixaRomanosAnterior = null;
        $nivelAnterior = null;
        $cor = "tipo1";
        $contador = 0;

        # Define o número de padrões de acordo com a tabela
        if ($idPlano == 13) {
            $numPadroes = 1;
            $temLetra = false;
        } elseif ($idPlano == 12) {
            $numPadroes = 10;
            $temLetra = false;
        } else {
            $numPadroes = 5;
            $temLetra = true;
        }

        # Se for relatório
        if ($relatorio) {

            # Exibe quadro do plano com o relatório true
            $this->exibeDadosPlano($idPlano, true);
            br();

            # Inicia a tabela do relatório
            echo '<table class="tabelaRelatorioFicha" border="0"';
        } else {
            # Exibe quadro do plano
            $this->exibeDadosPlano($idPlano);

            # Exibe a tabela de valores
            echo "<table class='tabelaPadrao'>";
            echo '<caption>Valores</caption>';
        }

        # Percorre os valores seguindo a ordem dos níveis definido no array
        foreach ($nivel as $nn) {

            # Pega os valores
            $select = "SELECT faixa,
                              valor,
                              idClasse,
                              tbtipocargo.cargo
                         FROM tbclasse LEFT JOIN tbtipocargo USING (idTipoCargo)
                        WHERE idPlano = {$idPlano} AND tbclasse.nivel = '{$nn}' ORDER BY SUBSTRING(faixa, 1, 1), valor";

            $row = $pessoal->select($select);

            # Preenche a tabela
            foreach ($row as $rr) {

                $faixa = $rr['faixa'];
                $valor = $rr['valor'];
                $cargo = $rr[3];

                # Trata faixa
                $parte = explode("-", $faixa);
                if ($temLetra) {
                    $letra = substr($parte[0], 0, 1);
                    $faixaRomanos = substr($parte[0], 1);
                } else {
                    $faixaRomanos = substr($parte[0], 0);
                }

                # Verifica se é pulo de linha
                if ($faixaRomanosAnterior <> $faixaRomanos) {

                    # Muda a cor da linha
                    if ($nivelAnterior <> $nn) {
                        if ($cor == "tipo1") {
                            $cor = "tipo2";
                        } else {
                            $cor = "tipo1";
                        }

                        $nivelAnterior = $nn;
                    }

                    /*
                     * Cabeçalho
                     */

                    # Verifica se é início da tabela
                    if (is_null($faixaRomanosAnterior)) {
                        echo "<tr>";

                        # Nível - Cabeçalho
                        if ($temLetra) {
                            echo "<th rowspan='2' colspan='2' valign='middle'>Nível</th>";
                        } else {
                            echo "<th rowspan='2' valign='middle'>Nível</th>";
                        }

                        # Faixas - Cabeçalho
                        echo "<th rowspan='2' valign='middle'>Faixa</th>";

                        # Padrões - Cabeçalho
                        if ($relatorio) {
                            echo "<td id='salarial' colspan='$numPadroes' valign='middle'>Padrão</td>";
                        } else {
                            echo "<th colspan='$numPadroes' valign='middle'>Padrão</th>";
                        }
                        echo "</tr>";

                        # Números dos Padrões - Cabeçalho
                        echo "<tr>";
                        for ($a = 1; $a <= $numPadroes; $a++) {
                            echo "<th>$a</th>";
                        }
                        echo "</tr>";
                        echo "<tr id='$cor'>";
                    } else {
                        echo "</tr>";
                        echo "<tr id='$cor'>";
                    }

                    /*
                     *  Corpo da tabela
                     */

                    $faixaRomanosAnterior = $faixaRomanos;

                    # Nível
                    echo "<td align='left'>$nn<br/>";
                    p($cargo, "ptabelaSalarial");
                    echo"</td>";

                    # Nível - Letra
                    if ($temLetra) {
                        echo "<td align='center'>$letra</td>";
                    }

                    # Faixa
                    echo "<td align='center'>$faixaRomanos</td>";

                    # Padrões - Valores
                    echo "<td align='right'>";
                    echo formataMoeda($valor);
                    echo "</td>";
                } else {
                    # Padrões - Valores
                    echo "<td align='right'>";
                    echo formataMoeda($valor);
                    echo "</td>";
                }
            }
        }

        echo "</tr></table>";
    }

    ###########################################################

    public function get_planoVigente($data = null, $idServidor = null) {
        /**
         * Retorna o id do plano de cargos que estava vigente na data indicada para o servidor indicado
         * 
         * @param $data       date    null A data desejada
         * @param $idServidor integer null O id do servidor analizado
         * 
         * @syntax $plano->get_planoVigente($data);
         *
         * @Obs O id Servidor é necessário pois existem planos que só são válidos para determinados cargos. Se não for fornecido isso será ignorado.
         * @Obs Se a data estiver nulla será considerada a data atual
         * @Obs Utilizada na rotina de progressão para saber em qual plano o servidor foi progredido
         */
        # Conecta
        $pessoal = new Pessoal();

        # Verifica se a data é nula
        if (is_null($data)) {
            $data = date("d/m/Y");
        }

        # Verifica se o idServidor foi fornecido
        if (!is_null($idServidor)) {
            # Verifica se servidor é professor ou adm e Tec
            $tipo = $pessoal->get_cargoTipo($idServidor);
        }

        # Pega os projetos cadastrados
        $select = 'SELECT idPlano
                     FROM tbplano
                     WHERE dtVigencia <= "' . date_to_bd($data) . '"
                     AND idPlano <> 6';

        if (!is_null($idServidor)) {
            $select .= ' AND (servidores = "Todos" OR servidores = "' . $tipo . '")';
        }

        $select .= ' ORDER BY dtVigencia desc limit 1';

        $row = $pessoal->select($select, false);
        return $row[0];
    }

    ###########################################################

    public function get_salarioClasse($idPlano = null, $classe = null) {
        /**
         * Retorna o salário cadastrado da classe do idPlano fornecido
         * 
         * @param $idPlano integer null O id do plano
         * @param $classe  texto   null A classe do salário
         * 
         * @syntax $plano->get_salarioClasse($idPlano, $classe);
         */
        # Pega os projetos cadastrados
        $select = 'SELECT valor
                     FROM tbclasse
                     WHERE faixa = "' . $classe . '"
                     AND idPlano = "' . $idPlano . '"';

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);
        return $row[0];
    }

    ###########################################################

    public function get_idClasse($idPlano = null, $classe = null) {
        /**
         * Retorna o salário cadastrado da classe do idPlano fornecido
         * 
         * @param $idPlano integer null O id do plano
         * @param $classe  texto   null A classe do salário
         * 
         * @syntax $plano->get_salarioClasse($idPlano, $classe);
         */
        # Pega os projetos cadastrados
        $select = 'SELECT idClasse
                     FROM tbclasse
                     WHERE faixa = "' . $classe . '"
                     AND idPlano = "' . $idPlano . '"';

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);
        return $row[0];
    }

    ###########################################################

    public function evibeValor($idClasse) {
        /**
         * Exive informação da faixa, valor e plano referente ao idClasse para ser exibido na rotina de cadastro de progressão
         * 
         * @param $classe  texto   null A classe do salário
         * 
         * @syntax $plano->evibeValor($idclasse);
         */
        if (is_null($idClasse)) {
            return null;
        } else {
            # Pega os projetos cadastrados
            $select = "SELECT faixa,
                              valor,
                              tbplano.numdecreto
                         FROM tbclasse LEFT JOIN tbplano USING (idPlano)
                         WHERE idClasse = $idClasse";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);

            pLista(
                    $row[0] . " - R$ " . formataMoeda($row[1]),
                    $row[2]
            );
        }
    }

    ###########################################################

    public function get_valor($idClasse) {
        /**
         * Retorna o valor do salário dessa idClasse
         * 
         * @param $classe  texto   null A classe do salário
         * 
         * @syntax $plano->evibeValor($idclasse);
         */
        if (is_null($idClasse)) {
            return null;
        } else {
            # Pega os projetos cadastrados
            $select = "SELECT valor
                         FROM tbclasse
                         WHERE idClasse = $idClasse";

            $pessoal = new Pessoal();
            $row = $pessoal->select($select, false);

            return $row[0];
        }
    }

    ###########################################################

    public function menuPlanos($idPlano) {
        /**
         * Exibe o menu de Planos de Cargo.
         * 
         * @syntax PlanoCargos::menuPlanos;
         */
        # Acessa o banco de dados
        $pessoal = new Pessoal();

        # Pega os projetos cadastrados
        $select = 'SELECT idPlano,
                          numDecreto
                     FROM tbplano
                  ORDER BY dtVigencia desc';

        $dados = $pessoal->select($select);
        $num = $pessoal->count($select);

        # Inicia o menu
        $menu1 = new Menu();
        $menu1->add_item('titulo1', 'Planos', '?fase=menuCaderno');
        #$menu1->add_item('sublink','+ Novo Caderno','?fase=cadernoNovo');
        # Verifica se tem Planos
        if ($num > 0) {
            # Percorre o array 
            foreach ($dados as $valor) {

                # Marca o item que está sendo editado
                if ($idPlano == $valor[0]) {
                    $menu1->add_item('link', "<b>" . $valor[1] . "</b>", '?id=' . $valor[0], $valor[1]);
                } else {
                    $menu1->add_item('link', $valor[1], '?id=' . $valor[0], $valor[1]);
                }
            }
        }
        $menu1->show();
    }

    ###########################################################

    public function exibeLei($idPlano) {
        /**
         * Exibe um link para a lei quando o campo link tiver sido preenchido
         * 
         * @param $idPlano integer null O id do plano
         * 
         * @syntax $plano->exibeLei($idPlano);
         */
        # Pega os projetos cadastrados
        $select = 'SELECT link
                     FROM tbplano
                     WHERE idPlano = ' . $idPlano;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select, false);

        if (is_null($row[0])) {
            echo "-";
        } else {
            $link = new Link(null, "../_legislacao/" . $row[0], "Exibe a Lei");
            $link->set_imagem(PASTA_FIGURAS_GERAIS . "do.png", 20, 20);
            $link->set_target("_blank");
            $link->show();
        }
    }

    ###########################################################

    public function exibeBotaoTabela($idPlano) {
        /**
         * Exibe um link para a lei quando o campo link tiver sido preenchido
         * 
         * @param $idPlano integer null O id do plano
         * 
         * @syntax $plano->exibeLei($idPlano);
         */
        $link = new Link(null, "?fase=exibeTabela&id=" . $idPlano, "Exibe a tabela SalariaL");
        $link->set_imagem(PASTA_FIGURAS_GERAIS . "tabela.png", 20, 20);
        $link->set_target("_blank");
        $link->show();
    }

    ###########################################################

    public function get_ultimoIdClasse($idCargo) {
        /**
         * Exibe o último idClasse do plano vigente para o idCargo informado
         * 
         * @syntax PlanoCargos->get_ultimoIdClasse("Elementar");
         */
        # Acessa o banco de dados
        $pessoal = new Pessoal();

        ########
        # Pega o plano atual        
        $idPlano = $this->get_planoAtual();

        ########
        # Pega o idTipoCargo desse idCargo
        #$idTipoCargo = $pessoal->get_idTipoCargo($idCargo);
        ########
        # Pega o nível do idTipoCargo
        $nivel = $pessoal->get_nivelCargoCargo($idCargo);

        # Pega o uĺtimo idClasse do idPlano atual e do nível informado
        $select = 'SELECT idClasse
                     FROM tbclasse
                    WHERE idPlano = ' . $idPlano . '
                      AND nivel = "' . $nivel . '" ';

        if ($idCargo == 128) {
            $select .= ' AND (SUBSTRING(faixa, 1, 1) = "E" OR faixa = "Associado" OR SUBSTRING(faixa, 1, 1) = "I")';
        }

        if ($idCargo == 129) {
            $select .= ' AND (SUBSTRING(faixa, 1, 1) = "F" OR faixa = "Titular" OR SUBSTRING(faixa, 1, 1) = "X")';
        }

        $select .= ' ORDER BY valor desc';

        $row = $pessoal->select($select, false);

        if (empty($row[0])) {
            return null;
        } else {
            return $row[0];
        }
    }

    ###########################################################

    public function get_planoAtual() {
        /**
         * Retorna o id do plano de cargos Atualmente vigente
         * 
         * @syntax $plano->get_planoAtual();
         */
        # Conecta
        $pessoal = new Pessoal();

        # Pega os projetos cadastrados
        $select = 'SELECT idPlano
                     FROM tbplano
                     WHERE planoAtual';

        $row = $pessoal->select($select, false);
        return $row[0];
    }

    ###########################################################

    public function evibeValorServidor($idServidor) {
        /**
         * Exive informação da faixa, valor e plano referente ao idClasse para ser exibido na rotina de cadastro de progressão
         * 
         * @param $idServidor  numero  null O id do servidor
         * 
         * @syntax $plano->evibeValor($idclasse);
         */
        ############################################## Parei aqui


        if (is_null($idServidor)) {
            return null;
        } else {

            # Pega o idClasse
            $pessoal = new Pessoal();
            $select1 = "SELECT idClasse
                          FROM tbprogressao
                         WHERE idServidor = {$idServidor}
              ORDER BY dtInicial desc";

            $row1 = $pessoal->select($select1, false);

            # Pega os projetos cadastrados
            $select2 = "SELECT faixa,
                              valor,
                              tbplano.numdecreto
                         FROM tbclasse LEFT JOIN tbplano USING (idPlano)
                         WHERE idClasse = $row1[0]";

            $row2 = $pessoal->select($select2, false);

            pLista(
                    $row2[0] . " - R$ " . formataMoeda($row2[1]),
                    $row2[2]
            );
        }
    }

    ###########################################################

    public function evibeValorServidorPlano($texto) {
        /**
         * Exibe o slaáriode um servidor na mesma faixa do atual(usado na rotina de importação de valores)
         * É necessário que a faixa seja a mesma
         * 
         * @param $idServidor  numero  null O id do servidor
         * @param $idPlano     numero  null O id do plano em que o servidor será transferido
         * 
         * @syntax $plano->evibeValorServidorPlano($idServidor, $idPlano);
         */
        if (is_null($texto)) {
            return null;
        } else {

            # Divide o texto TIPO&ID
            $pedaco = explode("&", $texto);

            # Pega os pedaços
            $idServidor = $pedaco[0];
            $idPlano = $pedaco[1];

            # Inicia as Classes
            $pessoal = new Pessoal();
            $plano = new PlanoCargos();

            # Pega o plano de cargos do servidor
            $idPlanoServidor = $this->get_idPlano($idServidor);

            # Pega o plano de cargos do servidor
            $idPlanoAtual = $this->get_planoAtual();

            # Verifica se o plano do servidor está atualizado
            if ($idPlanoServidor == $idPlanoAtual) {

                # Pega a faixa atual do servidor
                $faixa = $pessoal->get_faixaServidor($idServidor);

                # Pega o idClasse que iria no plano informado
                $idClasse = $plano->get_idClasse($idPlano, $faixa);

                # Pega o salário projetado
                $select2 = "SELECT faixa,
                               valor,
                               tbplano.numdecreto
                          FROM tbclasse LEFT JOIN tbplano USING (idPlano)
                         WHERE idClasse = {$idClasse}";

                $row2 = $pessoal->select($select2, false);

                pLista(
                        $row2[0] . " - R$ " . formataMoeda($row2[1]),
                        $row2[2]
                );
            } else {
                return null;
            }
        }
    }

    ###########################################################

    public function get_idPlano($idServidor) {
        /**
         * Retorna o idPlano do salário atual do servidor
         * 
         * @param $idServidor  numero  null O id do servidor
         * 
         * @syntax $plano->get_idPlano($idclasse);
         */
        ############################################## Parei aqui


        if (is_null($idServidor)) {
            return null;
        } else {

            # Pega o idClasse
            $pessoal = new Pessoal();
            $select1 = "SELECT idClasse
                          FROM tbprogressao
                         WHERE idServidor = {$idServidor}
              ORDER BY dtInicial desc";

            $row1 = $pessoal->select($select1, false);

            # Pega os projetos cadastrados
            $select2 = "SELECT idPlano
                          FROM tbclasse LEFT JOIN tbplano USING (idPlano)
                         WHERE idClasse = $row1[0]";

            $row2 = $pessoal->select($select2, false);

            return $row2[0];
        }
    }

    ###########################################################
}
