<?php

class Despacho {

    /**
     * Monta um Ato do Reitor
     * 
     * @author André Águia (Alat) - alataguia@gmail.com
     * 
     */
    private $data = null;
    private $texto = null;
    private $destino = null;
    private $origemNome = null;
    private $origemSetor = null;
    private $origemDescricao = null;
    private $origemLotacao = null;
    private $origemIdFuncional = null;
    private $reitor = null;
    private $saltoRodape = 3;
    private $rodapeNome = "Gerência de Recursos Humanos - GRH";
    private $rodapeEndereco = "Av. Alberto Lamego, 2000 – Prédio E-1  - Sala 217 -  CEP 28.013-602 -  Campos dos Goytacazes - RJ";
    private $rodapeTelefone = "Telefones: (22) 2739-7064  (22) 2748-6008  (22) 2748-6073";

    # do menu do relatório
    private $formCampos = null;    // array com campos para o formulario
    private $formLink = null;    // para onde vai o post

    ###########################################################

    public function __construct() {
        /**
         * Inicia a Ci e preenche oas variáveis com valores padrão
         * 
         */
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

    public function set_texto($texto) {
        /**
         * Inclui um objeto Input ao formulário
         * 
         * @syntax $form->add_item($objeto);
         * 
         * @param $controle object null Objeto Input a ser inserido no Formulário
         * 
         */
        $this->texto[] = $texto;
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
        p("<b>{$this->rodapeNome}</b><br/>{$this->rodapeEndereco}<br/>{$this->rodapeTelefone}", "pCiRodape");

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
        
        # Abre uma classe de menu do relatório
        if (!empty($this->formCampos)) {
            $menuRelatorio->set_formLink($this->formLink);
        }
        
        $menuRelatorio->show();        

        # Cabeçalho do Relatório (com o logotipo)
        $relatorio = new Relatorio();
        $relatorio->exibeCabecalho();

        # Limita o tamanho da tela
        $grid = new Grid("center");
        $grid->abreColuna(11);
        br();

        # Destino
        p($this->destino, 'pDestino');
        br();

        # Texto
        foreach ($this->texto as $textoDespacho) {
            p($textoDespacho, 'pDespacho');
        }

        br($this->saltoRodape);

        # Assinatura
        $textoAssinatura = "{$this->origemNome}<br/>";
        
        if (!empty($this->origemDescricao)) {
            $textoAssinatura .= "{$this->origemDescricao}<br/>";
        }
        
        if (!empty($this->origemLotacao)) {
            $textoAssinatura .= "{$this->origemLotacao}<br/>";
        }
        
        $textoAssinatura .= "Id Funcional n° {$this->origemIdFuncional}";
        
        p($textoAssinatura, 'pCiAssinatura');

        $grid->fechaColuna();
        $grid->fechaGrid();

        #$this->rodape();
    }
}
