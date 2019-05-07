<?php
class AtoReitor{
 /**
  * Monta um Ato do Reitor
  * 
  * @author André Águia (Alat) - alataguia@gmail.com
  * 
  */
    
    private $data = NULL;
    private $textoReitor = NULL;
    private $textoPrincipal = NULL;
    
    private $reitor = NULL;    
    private $saltoRodape = 3;
    
    ###########################################################
    
    public function __construct(){
    /**
     * Inicia a Ci e preenche oas variáveis com valores padrão
     */
        
        # Conecta ao banco de dados
        $pessoal = new Pessoal();
        
    	# Gerente do GRH (id 66)
        $idReitor = $pessoal->get_reitor();
        $nomeReitor = $pessoal->get_nome($idReitor);
    
        # Valores padrão de Origem
        $this->reitor = $nomeReitor;
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
    
    public function __call ($metodo, $parametros){
        ## Se for set, atribui um valor para a propriedade
        if (substr($metodo, 0, 3) == 'set'){
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
    
    private function rodape(){
        br($this->saltoRodape);
        hr();
        p('<b>'.$this->rodapeNome.'</b><br/>'.$this->rodapeEndereco.'<br/>Telefone: '.$this->rodapeTelefone,'pCiRodape');
    }
    
    ###########################################################
    
    public function show(){
    /**
     * Exibe a Ci
     * 
     * @syntax $ci->show();
     */
    
        ## Monta o Relatório 
        # Menu
        $menuRelatorio = new menuRelatorio();
        $menuRelatorio->set_botaoVoltar(NULL);    
        $menuRelatorio->show();

        # Cabeçalho do Relatório (com o logotipo)
        $relatorio = new Relatorio();
        $relatorio->exibeCabecalho();

        hr();

        # Limita o tamanho da tela
        $grid = new Grid("center");
        $grid->abreColuna(11);
        br(2);
    
        # Declaração
        p('ATO DO REITOR','pDeclaracaoTitulo');
        p('DE '.$this->data,'pDeclaracaoTitulo');
        br(2);

        # Texto reitor
        p($this->textoReitor,'pCi');
        br(2);
        
        # Texto principal
        p($this->textoPrincipal,'pCi');
        br(2);
        
        # Data
        p('Campos dos Goytacazes, '.dataExtenso($this->data),'pDeclaracaoData');
        br(3);

        # Assinatura
        #p('____________________________________________________','pCiAssinatura');
        p('<b>'.$this->reitor.'<br/>REITOR</b>','pCiAssinatura');

        $this->rodape();
        
        $grid->fechaColuna();
        $grid->fechaGrid();
    }
}