<?php

class AposentadoriaCompulsoria {

    /**
     * Abriga as várias rotina referentes a aposentadoria do servidor
     * 
     * @author André Águia (Alat) - alataguia@gmail.com  
     */
    private $idServidor = null;
    private $calculoInicial = "Média aritmética simples das 80% maiores remunerações de contribuição";
    private $teto = "Remuneração do servidor no cargo efetivo";
    private $reajuste = "INPC – LEI 6.244/2012";
    private $paridade = "SEM PARIDADE";

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

    public function exibeAnalise() {

        # Pega os dados do servidor
        $pessoal = new Pessoal();
        $idadeServidor = $pessoal->get_idade($this->idServidor);
        
        # Pega a idade da regr
        $intra = new Intra();
        $idade = $intra->get_variavel("aposentadoria.compulsoria.idade");

        $hoje = date("d/m/Y");

        /*
         *  Análise
         */

        # Idade
        if ($idadeServidor >= $idade) {
            $analiseIdade = "OK";
        } else {
            # Pega a data de nascimento (vem dd/mm/AAAA)
            $dtNasc = $pessoal->get_dataNascimento($this->idServidor);

            # Calcula a data
            $novaData = addAnos($dtNasc, $idade);
            $analiseIdade = "Somente em {$novaData}.";
        }

        /*
         * Descrição
         */

        $idadeDescricao = "Idade do servidor.";

        /*
         *  Tabela
         */

        # Limita o tamanho da tela
        $grid = new Grid();
        $grid->abreColuna(12);

        tituloTable("Aposentadoria Compulsória");
        br();

        $grid->fechaColuna();
        $grid->abreColuna(8);

        $array = [
            ["Idade", $idadeDescricao, "{$idade} anos", "{$idadeServidor} anos", $analiseIdade],
        ];

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_titulo("Requisitos");
        $tabela->set_conteudo($array);
        $tabela->set_label(array("Item", "Descrição", "Regra", "Servidor", "Análise"));
        $tabela->set_width(array(20, 25, 15, 15, 25));
        $tabela->set_align(array("left", "left"));
        $tabela->set_totalRegistro(false);
        $tabela->set_formatacaoCondicional(array(
            array('coluna' => 4,
                'valor' => 'OK',
                'operador' => '=',
                'id' => 'emAberto'),
            array('coluna' => 4,
                'valor' => 'OK',
                'operador' => '<>',
                'id' => 'arquivado')
        ));
        $tabela->show();

        $grid->fechaColuna();
        $grid->abreColuna(4);

        # Exibe outras informações
        $array = [
            ["Cálculo Inicial", $this->calculoInicial],
            ["Teto", $this->teto],
            ["Reajuste", $this->reajuste],
            ["Paridade", $this->paridade]
        ];

        # Exibe a tabela
        $tabela = new Tabela();
        $tabela->set_titulo("Remuneração");
        $tabela->set_conteudo($array);
        $tabela->set_label(array("Item", "Descrição"));
        $tabela->set_width(array(30, 70));
        $tabela->set_align(array("left", "left"));
        $tabela->set_totalRegistro(false);
        $tabela->show();

        $grid->fechaColuna();
        $grid->fechaGrid();
    }
}
