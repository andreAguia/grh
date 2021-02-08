<?php

class Estatistica {

    /**
     * Abriga as várias rotina do Cadastro de Formação Escolar do servidor
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     */
    private $assunto = null;        // Qual o assunto da estatistica
    private $labelTabela = null;    // O label da tabela
    private $labelGrafico = null;   // O label do gráfico
    private $arrayTabela = null;    // o array da tabela
    private $arrayGrafico = null;   // o array do gráfico

    private $somenteAtivos = true;
    ###########################################################

    /**
     * Método Construtor
     */
    public function __construct($assunto = null,$somenteAtivos = true) {

        # insere o assunto
        $this->assunto = $assunto;
        $this->somenteAtivos = $somenteAtivos;
    }

    ###########################################################

    /**
     * Método pega os dados
     */
    private function get_dados() {

        switch ($this->assunto) {

            case "perfil" :

                $select = 'SELECT tbperfil.nome, count(tbservidor.idServidor) as grupo
                         FROM tbservidor LEFT JOIN tbperfil USING(idPerfil)
                        WHERE tbservidor.situacao = 1 
                     GROUP BY 1
                     ORDER BY 2 DESC ';

                $pessoal = new Pessoal();
                $servidores = $pessoal->select($select);

                # Coloca o array do label
                $this->labelTabela = array("Perfil", "Servidores");
                break;

            case "estadoCivil" :

                $select = 'SELECT tbestciv.estciv, count(tbservidor.idServidor) as grupo
                         FROM tbestciv RIGHT JOIN tbpessoa ON (tbestciv.idEstCiv = tbpessoa.estCiv)
                                             JOIN tbservidor USING (idPessoa)';
                if($this->somenteAtivos){
                    $select .= ' WHERE tbservidor.situacao = 1 ';
                }
                        
                $select .= ' GROUP BY 1 ORDER BY 2 DESC ';

                $pessoal = new Pessoal();
                $servidores = $pessoal->select($select);

                # Coloca o array do label
                $this->labelTabela = array("Estado Civil", "Servidores");
                break;

            case "cidade" :

                $select = 'SELECT CONCAT(tbcidade.nome," (",tbestado.uf,")"), count(tbservidor.idServidor) as grupo
                         FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                       JOIN tbcidade USING (idCidade)
                                       JOIN tbestado USING (idEstado)';
                if($this->somenteAtivos){
                    $select .= ' WHERE tbservidor.situacao = 1 ';
                }
                        
                $select .= ' GROUP BY 1 ORDER BY 2 DESC ';

                $pessoal = new Pessoal();
                $servidores = $pessoal->select($select);

                # Coloca o array do label
                $this->labelTabela = array("Cidade", "Servidores");
                break;

            case "nacionalidade" :

                $select = 'SELECT tbnacionalidade.nacionalidade, count(tbservidor.idServidor) as grupo
                         FROM tbnacionalidade JOIN tbpessoa ON(tbnacionalidade.idnacionalidade = tbpessoa.nacionalidade)
                                              JOIN tbservidor USING (idPessoa)';
                if($this->somenteAtivos){
                    $select .= ' WHERE tbservidor.situacao = 1 ';
                }
                        
                $select .= ' GROUP BY 1 ORDER BY 2 DESC ';

                $pessoal = new Pessoal();
                $servidores = $pessoal->select($select);

                # Coloca o array do label
                $this->labelTabela = array("Nacionalidade", "Servidores");
                break;
        }

        # Soma a coluna do count
        $total = array_sum(array_column($servidores, "grupo"));

        # Passa para p gráfico sem o total
        $this->arrayGrafico = $servidores;

        # Coloca o total para a tabela
        $servidores[] = array("Total", $total);
        $this->arrayTabela = $servidores;
    }

    ###########################################################

    /**
     * Método get_tabelaSimples
     */
    public function exibeTabelaSimples() {

        # Pega os dados
        $this->get_dados();

        $tabela = new Tabela();
        $tabela->set_conteudo($this->arrayTabela);
        $tabela->set_titulo($this->tabelatitulo);
        $tabela->set_label($this->labelTabela);
        $tabela->set_width(array(80, 20));
        $tabela->set_align(array("left", "center"));
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                'valor' => "Total",
                'operador' => '=',
                'id' => 'estatisticaTotal')));
        $tabela->show();
    }

    ###########################################################

    /**
     * Método get_graficoSimples
     */
    public function exibeGraficoSimples($tamanho = 300) {

        # Pega os dados
        $this->get_dados();

        # Gráfico
        $chart = new Chart("Pie", $this->arrayGrafico);
        $chart->set_idDiv("grafico");
        $chart->set_legend(false);
        $chart->set_tamanho($tamanho, $tamanho);
        $chart->show();
    }

    ###########################################################

    /**
     * Método pega os dados
     */
    private function get_dadosPorSexo() {

        switch ($this->assunto) {

            case "perfil" :

                # Select
                $select = 'SELECT tbperfil.nome, tbpessoa.sexo, count(tbservidor.idServidor) as grupo
                         FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                       JOIN tbperfil USING (idPerfil)';
                if($this->somenteAtivos){
                    $select .= ' WHERE tbservidor.situacao = 1 ';
                }
                        
                $select .= ' GROUP BY 1, tbpessoa.sexo
                     ORDER BY grupo desc';

                $this->labelTabela = array("Perfil", "Feminino", "Masculino", "Total");
                $this->labelGrafico = array("Perfil", "Feminino", "Masculino");
                break;

            case "estadoCivil" :

                # Select
                $select = 'SELECT tbestciv.estciv, tbpessoa.sexo, count(tbservidor.idServidor) as grupo
                         FROM tbestciv RIGHT JOIN tbpessoa ON (tbestciv.idEstCiv = tbpessoa.estCiv)
                                             JOIN tbservidor USING (idPessoa)';
                if($this->somenteAtivos){
                    $select .= ' WHERE tbservidor.situacao = 1 ';
                }
                        
                $select .= ' GROUP BY 1, tbpessoa.sexo
                     ORDER BY tbestciv.estciv';

                $this->labelTabela = array("Estado Civil", "Feminino", "Masculino", "Total");
                $this->labelGrafico = array("Estado Civil", "Feminino", "Masculino");
                break;

            case "nacionalidade" :

                # Select
                $select = 'SELECT tbnacionalidade.nacionalidade, tbpessoa.sexo, count(tbservidor.idServidor) as grupo
                         FROM tbnacionalidade JOIN tbpessoa ON(tbnacionalidade.idnacionalidade = tbpessoa.nacionalidade)
                                              JOIN tbservidor USING (idPessoa)';
                if($this->somenteAtivos){
                    $select .= ' WHERE tbservidor.situacao = 1 ';
                }
                        
                $select .= 'GROUP BY 1, tbpessoa.sexo
                     ORDER BY tbnacionalidade.nacionalidade';
                     

                $this->labelTabela = array("Nacionalidade", "Feminino", "Masculino", "Total");
                $this->labelGrafico = array("Nacionalidade", "Feminino", "Masculino");
                break;
        }

        # Pega os valores
        $pessoal = new Pessoal();
        $servidores = $pessoal->select($select);

        # Novo array 
        $arrayResultado = array();
        $arrayGrafico = array();

        # Valores anteriores
        $valorAnterior = null;

        # inicia as variáveis
        $masc = 0;
        $femi = 0;
        $totalMasc = 0;
        $totalFemi = 0;
        $total = 0;

        # Modelar o novo array
        foreach ($servidores as $value) {
            # Carrega as variáveis
            $valor = $value[0];
            $sexo = $value[1];
            $contagem = $value[2];

            # Verifica se mudou de valor
            if ($valor <> $valorAnterior) {

                if (is_null($valorAnterior)) {
                    $valorAnterior = $valor;
                } else {
                    # joga para os arrays
                    $arrayResultado[] = array($valorAnterior, $femi, $masc, $femi + $masc);
                    $arrayGrafico[] = array($valorAnterior, $femi, $masc);

                    $masc = 0;
                    $femi = 0;

                    $valorAnterior = $valor;
                    $total += ($femi + $masc);
                }
            }

            if ($sexo == 'Masculino') {
                $masc = $contagem;
                $totalMasc += $masc;
            } else {
                $femi = $contagem;
                $totalFemi += $femi;
            }
        }

        $arrayResultado[] = array($valorAnterior, $femi, $masc, $femi + $masc);
        $arrayGrafico[] = array($valorAnterior, $femi, $masc);

        # Soma a coluna do count
        $totalColuna = array_sum(array_column($servidores, "grupo"));

        $arrayResultado[] = array("Total", $totalFemi, $totalMasc, $totalColuna);


        $this->arrayTabela = $arrayResultado;
        $this->arrayGrafico = $arrayGrafico;
    }

    ###########################################################

    /**
     * Método pega os dados
     */
    private function get_dadosPorTipoCargo() {

        switch ($this->assunto) {

            case "perfil" :

                # Select
                $select = 'SELECT tbperfil.nome, tbtipocargo.tipo, count(tbservidor.idServidor) as grupo
                         FROM tbpessoa JOIN tbservidor USING (idPessoa)
                                       JOIN tbcargo USING (idCargo)
                                       JOIN tbtipocargo USING (idTipoCargo)
                                       JOIN tbperfil USING (idPerfil)
                                       WHERE (idPerfil = 1 OR idPerfil = 2)';
                if($this->somenteAtivos){
                    $select .= ' AND tbservidor.situacao = 1 ';
                }
                        
                $select .= '
                     GROUP BY 1, tbtipocargo.tipo
                     ORDER BY grupo desc';

                $this->labelTabela = array("Perfil", "Adm/Tec", "Professor", "Total");
                $this->labelGrafico = array("Perfil", "Adm/Tec", "Professor");
                break;

            case "estadoCivil" :

                # Select
                $select = 'SELECT tbestciv.estciv, tbtipocargo.tipo, count(tbservidor.idServidor) as grupo
                         FROM tbestciv RIGHT JOIN tbpessoa ON (tbestciv.idEstCiv = tbpessoa.estCiv)
                                             JOIN tbservidor USING (idPessoa)
                                             JOIN tbcargo USING (idCargo)
                                             JOIN tbtipocargo USING (idTipoCargo)
                                       WHERE (idPerfil = 1 OR idPerfil = 2)';
                if($this->somenteAtivos){
                    $select .= ' AND tbservidor.situacao = 1 ';
                }
                        
                $select .= '
                     GROUP BY 1,  tbtipocargo.tipo
                     ORDER BY tbestciv.estciv';

                $this->labelTabela = array("Estado Civil", "Adm/Tec", "Professor", "Total");
                $this->labelGrafico = array("Estado Civil", "Adm/Tec", "Professor");
                break;

            case "nacionalidade" :

                # Select
                $select = 'SELECT tbnacionalidade.nacionalidade, tbtipocargo.tipo, count(tbservidor.idServidor) as grupo
                         FROM tbnacionalidade JOIN tbpessoa ON(tbnacionalidade.idnacionalidade = tbpessoa.nacionalidade)
                                              JOIN tbservidor USING (idPessoa)
                                              JOIN tbcargo USING (idCargo)
                                              JOIN tbtipocargo USING (idTipoCargo)
                                       WHERE (idPerfil = 1 OR idPerfil = 2)';
                if($this->somenteAtivos){
                    $select .= ' AND tbservidor.situacao = 1 ';
                }
                        
                $select .= '
                     GROUP BY 1, tbtipocargo.tipo
                     ORDER BY tbnacionalidade.nacionalidade';

                $this->labelTabela = array("Nacionalidade", "Adm/Tec", "Professor", "Total");
                $this->labelGrafico = array("Nacionalidade", "Adm/Tec", "Professor");
                break;
        }

        # Pega os valores
        $pessoal = new Pessoal();
        $servidores = $pessoal->select($select);

        # Novo array 
        $arrayResultado = array();
        $arrayGrafico = array();

        # Valores anteriores
        $valorAnterior = null;

        # inicia as variáveis
        $masc = 0;
        $femi = 0;
        $totalMasc = 0;
        $totalFemi = 0;
        $total = 0;

        # Modelar o novo array
        foreach ($servidores as $value) {
            # Carrega as variáveis
            $valor = $value[0];
            $sexo = $value[1];
            $contagem = $value[2];

            # Verifica se mudou de valor
            if ($valor <> $valorAnterior) {

                if (is_null($valorAnterior)) {
                    $valorAnterior = $valor;
                } else {
                    # joga para os arrays
                    $arrayResultado[] = array($valorAnterior, $femi, $masc, $femi + $masc);
                    $arrayGrafico[] = array($valorAnterior, $femi, $masc);

                    $masc = 0;
                    $femi = 0;

                    $valorAnterior = $valor;
                    $total += ($femi + $masc);
                }
            }

            if ($sexo == 'Professor') {
                $masc = $contagem;
                $totalMasc += $masc;
            } else {
                $femi = $contagem;
                $totalFemi += $femi;
            }
        }

        $arrayResultado[] = array($valorAnterior, $femi, $masc, $femi + $masc);
        $arrayGrafico[] = array($valorAnterior, $femi, $masc);

        # Soma a coluna do count
        $totalColuna = array_sum(array_column($servidores, "grupo"));

        $arrayResultado[] = array("Total", $totalFemi, $totalMasc, $totalColuna);


        $this->arrayTabela = $arrayResultado;
        $this->arrayGrafico = $arrayGrafico;
    }

    ###########################################################

    /**
     * Método exibeTabelaPorSexo
     */
    public function exibeTabelaPorSexo($titulo = null) {

        # Pega os dados
        $this->get_dadosPorSexo();

        # Tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($this->arrayTabela);
        $tabela->set_titulo($titulo);
        $tabela->set_label($this->labelTabela);
        $tabela->set_align(array("left", "center", "center", "center"));
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                'valor' => "Total",
                'operador' => '=',
                'id' => 'estatisticaTotal')));
        $tabela->show();
    }

    ###########################################################

    /**
     * Método exibeGraficoPorSexo
     */
    public function exibeGraficoPorSexo() {

        # Calcula os dados
        $this->get_dadosPorSexo();

        $chart = new Chart("ColumnChart", $this->arrayGrafico, 2);
        $chart->set_cores(array("Violet", "CornflowerBlue"));
        $chart->set_idDiv("graficoPorSexo");
        $chart->set_label($this->labelGrafico);
        $chart->set_tituloEixoY("Servidores");
        $chart->set_tituloEixoX("Perfil");
        $chart->set_legend(false);
        $chart->show();
    }

    ###########################################################

    /**
     * Método exibeTabelaPorSexo
     */
    public function exibeTabelaPorTipoCargo($titulo = null) {

        # Pega os dados
        $this->get_dadosPorTipoCargo();

        # Tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($this->arrayTabela);
        $tabela->set_titulo($titulo);
        $tabela->set_label($this->labelTabela);
        $tabela->set_align(array("left", "center", "center", "center"));
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional(array(array('coluna' => 0,
                'valor' => "Total",
                'operador' => '=',
                'id' => 'estatisticaTotal')));
        $tabela->show();
    }

    ###########################################################
}
