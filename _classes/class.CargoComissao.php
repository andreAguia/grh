<?php
class CargoComissao{
 /**
  * Abriga as várias rotina do Cadastro de cargo em Comissao
  * 
  * @author André Águia (Alat) - alataguia@gmail.com  
  */
    
    
    ###########################################################
    
    /**
    * Método Construtor
    */
    public function __construct(){
        
    }

    ###########################################################
    
    function get_dados($idComissao){
        
    /**
     * fornece a próxima tarefa a ser realizada
     */
        
        # Pega os dados
        $select="SELECT *
                   FROM tbcomissao
                  WHERE idComissao = $idComissao";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        return $dados;
    }
    
    ###########################################################
    
    function get_descricaoCargo($idComissao){
        
    /**
     * fornece a próxima tarefa a ser realizada
     */
        
        # Pega os dados
        $select="SELECT tbdescricaocomissao.descricao
                   FROM tbdescricaocomissao JOIN tbcomissao USING (idDescricaoComissao)
                  WHERE idComissao = $idComissao";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        return $dados[0];
    }
    
    ###########################################################
    
    function get_cargoCompleto($idComissao){
        
    /**
     * fornece a próxima tarefa a ser realizada
     */
        
        # Pega os dados
        $select="SELECT tbtipocomissao.simbolo,
                        tbtipocomissao.descricao,
                        tbdescricaocomissao.descricao
                   FROM tbcomissao JOIN tbtipocomissao USING (idTipoComisso)
                                   JOIN descricaocomissao USING (idDescricaoComissao)
                  WHERE tbcomissao.idComissao = $idComissao";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        $retorno = $dados[0]." - ".$dados[1]."<br/>".$dados[2];
        return $retorno;
    }
    
    ###########################################################
    
    function get_numServidoresCargo($idTipoCargo){
        
    /**
     * 
     * Informa o número de servidores ativos nomeados para esse cargo
     * 
     */
        
        # Pega os dados
        $select = "SELECT tbservidor.idServidor
                     FROM tbservidor LEFT JOIN tbcomissao USING(idServidor)
                    WHERE tbcomissao.idTipoComissao = $idTipoCargo
                      AND situacao = 1
                      AND (tbcomissao.dtExo IS NULL OR CURDATE() < tbcomissao.dtExo)";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->count($select);
        return $dados;
    }
    
    ###########################################################

    /**
     * Método get_vagas
     * 
     * Exibe o n�mero de vagas em um determinado cargo em comissao
     */

    public function get_vagas($idTipoCargo)
    {
        $select = 'SELECT vagas                             
                     FROM tbtipocomissao 
                    WHERE idTipoComissao = '.$idTipoCargo;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);		
        return $row[0];
    }

    ###########################################################
    
    function exibeResumo($idTipoCargo){
        
    /**
     * Exibe um quadro com o resumo do tipo de cargo
     */
        
        # Pega os dados
        $dados = array();
        $vagas = $this->get_vagas($idTipoCargo);
        $nomeados = $this->get_numServidoresCargo($idTipoCargo);
        $dispoinivel = $vagas - $nomeados;
        
        $simbolo = $this->get_simbolo($idTipoCargo);
        $valor = $this->get_valor($idTipoCargo);
        
        # Pega dados da Classe Pessoal
        $pessoal = new Pessoal();
        $nomeCargo = $pessoal->get_nomeCargoComissao($idTipoCargo);
        

        # Coloca no array
        $dados[] = array($nomeCargo,$simbolo,"R$ ".formataMoeda($valor),$vagas,$nomeados,$dispoinivel);

        # Monta a tabela
        $tabela = new Tabela();
        $tabela->set_conteudo($dados);
        $tabela->set_label(array("Cargo","Símbolo","Valor","Vagas","Nomeados","Disponíveis"));
        $tabela->set_totalRegistro(FALSE);
        $tabela->set_align(array("center"));
        $tabela->set_titulo($nomeCargo);
        $tabela->set_formatacaoCondicional(array(array('coluna' => 5,
                                                    'valor' => 0,
                                                    'operador' => '<',
                                                    'id' => "comissaoVagasNegativas"),
                                             array('coluna' => 5,
                                                    'valor' => 0,
                                                    'operador' => '=',
                                                    'id' => "comissaoSemVagas"),
                                             array('coluna' => 5,
                                                    'valor' => 0,
                                                    'operador' => '>',
                                                    'id' => "comissaoSemVagas")));
        $tabela->show();
        
        # Exibe alerta de nomeação a maios que vagas
        if($nomeados > $vagas){
            $painel = new Callout("warning");
            $painel->abre();
            
            p("ATENÇÂO !!!<br/>Existem mais servidores nomeados que vagas !!<br/>$vagas Vagas<br/>$nomeados Servidores Nomeados","center");
            
            $painel->fecha();
        }
    }
    
    ###########################################################

    /**
     * Método get_simbolo
     * 
     * Exibe o símbolo de um determinado cargo em comissao
     */

    public function get_simbolo($idTipoCargo){
        
        $select = 'SELECT simbolo                             
                     FROM tbtipocomissao 
                    WHERE idTipoComissao = '.$idTipoCargo;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);		
        return $row[0];
    }

    ###########################################################

    /**
     * Método get_valor
     * 
     * Exibe o valor de um determinado cargo em comissao
     */

    public function get_valor($idTipoCargo){
        
        $select = 'SELECT valsal                             
                     FROM tbtipocomissao 
                    WHERE idTipoComissao = '.$idTipoCargo;

        $pessoal = new Pessoal();
        $row = $pessoal->select($select,FALSE);		
        return $row[0];
    }

    ###########################################################
}