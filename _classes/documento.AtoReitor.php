<?php

class AtoReitor {

    /**
     * Monta um Ato do Reitor
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     */
    private $data = null;
    private $textoReitor = null;
    private $textoPrincipal = null;
    private $reitor = null;
    private $generoReitor = null;
    private $saltoRodape = 3;
    private $rodapeNome = "Gerência de Recursos Humanos - GRH";
    private $rodapeEndereco = "Av. Alberto Lamego, 2000 – Prédio E-1  - Sala 217 -  CEP 28.013-602 -  Campos dos Goytacazes - RJ";
    private $rodapeTelefone = "(22) 2739-7064";

    ###########################################################

    public function __construct() {
        /**
         * Inicia a Ci e preenche oas variáveis com valores padrão
         */
        # Conecta ao banco de dados
        $pessoal = new Pessoal();

        # Valores padrão de Origem
        $this->reitor = $pessoal->get_nome($pessoal->get_reitor());
        $this->generoReitor = $pessoal->get_sexo($pessoal->get_reitor());
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

        $div = new Div('rodape');
        $div->abre();

        hr();
        p('<b>' . $this->rodapeNome . '</b><br/>' . $this->rodapeEndereco . '<br/>Telefone: ' . $this->rodapeTelefone, 'pCiRodape');

        $div->fecha();
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
        $menuRelatorio->set_botaoVoltar(null);
        $menuRelatorio->show();

        # Cabeçalho do Relatório (com o logotipo)
        $relatorio = new Relatorio();
        $relatorio->exibeCabecalho();

        hr();

        # Limita o tamanho da tela
        $grid = new Grid("center");
        $grid->abreColuna(11);
        br();

        # Declaração
        if ($this->generoReitor == "Masculino") {
            p('ATO DO REITOR', 'pAtoTitulo');
        } else {
            p('ATO DA REITORA', 'pAtoTitulo');
        }
        p('DE ' . $this->data, 'pAtoTitulo');
        br(2);

        # Texto reitor
        p($this->textoReitor, 'pAto');
        br();

        # Texto principal
        p($this->textoPrincipal, 'pAto');
        br();

        # Data
        p('Campos dos Goytacazes, ' . dataExtenso($this->data), 'pAtoData');
        br(2);

        # Assinatura
        #p('____________________________________________________','pCiAssinatura');

        if ($this->generoReitor == "Masculino") {
            p('<b>' . strtoupper($this->reitor) . '<br/>REITOR</b>', 'pCiAssinatura');
        } else {
            p('<b>' . strtoupper($this->reitor) . '<br/>REITORA</b>', 'pCiAssinatura');
        }

        $grid->fechaColuna();
        $grid->fechaGrid();

        $this->rodape();
    }
}
