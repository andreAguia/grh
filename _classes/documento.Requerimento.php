<?php

class Declaracao {

    /**
     * Monta uma Ci
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     * @var private $projeto        integer NULL O id do projeto a ser acessado
     * 
     */
    private $data = NULL;
    private $texto = NULL;
    private $destinatario = NULL;
    private $rodapeNome = "Gerência de Recursos Humanos - GRH";
    private $rodapeEndereco = "Av. Alberto Lamego, 2000 – Prédio E-1  - Sala 217 -  CEP 28.013-602 -  Campos dos Goytacazes - RJ";
    private $rodapeTelefone = "(22) 2739-7064";
    private $saltoRodape = 3;
    private $aviso = NULL;
    private $carimboCnpj = FALSE;

    ###########################################################

    public function __construct() {
        /**
         * Inicia a Ci e preenche oas variáveis com valores padrão
         */
        # Conecta ao banco de dados
        $pessoal = new Pessoal();
    }

    ###########################################################

    public function set_texto($texto) {
        /**
         * Inclui um objeto Input ao formulário
         * 
         * @syntax $form->add_item($objeto);
         * 
         * @param $controle object NULL Objeto Input a ser inserido no Formulário
         * 
         */
        $this->texto[] = $texto;
    }

    ###########################################################

    /**
     * Métodos get e set construídos de forma automática pelo 
     * metodo mágico __call.
     * Esse método cria um set e um get para todas as propriedades da classe.
     * Um método existente tem prioridade sobre os métodos criados pelo __call.
     * 
     * O formato dos métodos devem ser:
     * 	set_propriedade
     * 	get_propriedade
     * 
     * @param 	$metodo		O nome do metodo
     * @param 	$parametros	Os parâmetros inseridos  
     */
    public function __call($metodo, $parametros) {
        ## Se for set, atribui um valor para a propriedade
        if (substr($metodo, 0, 3) == 'set') {
            $var = substr($metodo, 4);
            $this->$var = $parametros[0];
        }

        # Se for Get, retorna o valor da propriedade
        #if (substr($metodo, 0, 3) == 'get')
        #{
        # $var = substr($metodo, 4);
        #  return $this->$var;
        #}
    }

    ###########################################################

    /**
     * Método rodape
     * 
     * Exibe o rodapé
     */
    private function rodape() {
        br($this->saltoRodape);
        hr();
        p('<b>' . $this->rodapeNome . '</b><br/>' . $this->rodapeEndereco . '<br/>Telefone: ' . $this->rodapeTelefone, 'pCiRodape');
    }

    ###########################################################

    public function show() {
        /**
         * Exibe a Ci
         * 
         * @syntax $ci->show();
         */
        ## Monta o Relatório 
        # Menu
        $menuRelatorio = new menuRelatorio();
        $menuRelatorio->set_botaoVoltar(NULL);
        $menuRelatorio->set_aviso($this->aviso);
        $menuRelatorio->show();

        # Cabeçalho do Relatório (com o logotipo)
        $relatorio = new Relatorio();
        $relatorio->exibeCabecalho();

        #hr();
        # Limita o tamanho da tela
        $grid = new Grid("center");
        $grid->abreColuna(11);
        br(2);

        # Destinatário
        p($this->destinatario, 'pCi');
        br(2);

        # Texto
        foreach ($this->texto as $textoCi) {
            p($textoCi, 'pCi');
        }

        if ($this->carimboCnpj) {
            $grid = new Grid();
            $grid->abreColuna(8);

            # Data
            br(2);
            p('Campos dos Goytacazes, ' . dataExtenso($this->data), 'pDeclaracaoData');

            $grid->fechaColuna();
            $grid->abreColuna(4);

            $figura = new Imagem(PASTA_FIGURAS . 'carimboCnpj.png', NULL, 200, 120);
            $figura->show();

            $grid->fechaColuna();
            $grid->fechaGrid();
        } else {
            # Data
            br(2);
            p('Campos dos Goytacazes, ' . dataExtenso($this->data), 'pDeclaracaoData');
        }
        br(3);

        # Assinatura
        #p('____________________________________________________','pCiAssinatura');
        p('Assinatura do Requerente', 'pCiAssinatura');

        $this->rodape();

        $grid->fechaColuna();
        $grid->fechaGrid();
    }

}
