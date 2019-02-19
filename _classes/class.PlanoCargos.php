<?php
class PlanoCargos{
 /**
  * Abriga as várias rotina do Cadastro de Planos de cargos e Tasbelas salariais
  * 
  * @author André Águia (Alat) - alataguia@gmail.com
  * 
  * @var private $projeto        integer NULL O id do projeto a ser acessado
  * 
  */
    
    private $idPlano = NULL;
    
    ###########################################################
    
    /**
    * Método Construtor
    */
    public function __construct(){
        
    }

    ###########################################################
    
    public function get_dadosPlano($idPlano = NULL){
    /**
     * Retorna um array com todas as informações do plano de cargos informado
     * 
     * @param $idPlano integer NULL o $idPlano
     * 
     * @syntax $plano->get_dadosPlano([$idPlano]);  
     */
    
        # Pega os projetos cadastrados
        $select = 'SELECT numDecreto,
                          dtPublicacao,
                          dtDecreto,
                          planoAtual,
                          link
                     FROM tbplano
                     WHERE idPlano = '.$idPlano;
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,false);
        return $row;
    }
    
    ###########################################################
    
    public function exibeTabela($idPlano = NULL, $editavel = FALSE){
    /**
     * Retorna a tabela salarial do plano
     * 
     * @param $idPlano integer NULL o $idPlano
     * 
     * @syntax $plano->exibeTabela([$idPlano]);  
     */
    
        # Conecta
        $pessoal = new Pessoal();
        
        # Define a ordem dos niveis
        $nivel = array("Elementar","Fundamental","Médio","Superior","Doutorado");
        
        # Define as variáveis secundárias
        $faixaRomanosAnterior = NULL;
        $nivelAnterior = NULL;
        $cor = "tipo1";
                
        # Define o número de padrões de acordo com a tabela
        if($idPlano == 13){
            $numPadroes = 1;
            $temLetra = FALSE;
        }elseif($idPlano == 12){
            $numPadroes = 10;
            $temLetra = FALSE;
        }else{
            $numPadroes = 5;
            $temLetra = TRUE;
        }
        
        # Inicia a Tabela
        echo "<table class='tabelaPadrao'>";
        
        # Percorre os valores seguindo a ordem dos níveis definido no array
        foreach ($nivel as $nn){
            
            # Pega os valores
            $select = 'SELECT faixa,
                              valor,
                              idClasse
                         FROM tbclasse
                        WHERE idPlano = '.$idPlano.' AND nivel = "'.$nn.'" ORDER BY SUBSTRING(faixa, 1, 1), valor';
            
            $row = $pessoal->select($select);
            
            # Preenche a tabela
            foreach ($row as $rr){
                
                $faixa = $rr[0];
                $valor = $rr[1];
                $url = $rr[2];
                
                # Trata faixa
                $parte = explode("-",$faixa);
                if($temLetra){
                    $letra = substr($parte[0],0,1);
                    $faixaRomanos = substr($parte[0],1);
                }else{
                    $faixaRomanos = substr($parte[0],0);
                }
                
                
                # Verifica se é pulo de linha
                if($faixaRomanosAnterior <> $faixaRomanos){
                    
                    # Muda a cor da linha
                    if($nivelAnterior <> $nn){
                        if($cor == "tipo1"){
                            $cor = "tipo2";
                        }else{
                            $cor = "tipo1";
                        }
                        
                        $nivelAnterior = $nn;
                    }
                    
                    # Verifica se é início da tabela
                    if(is_null($faixaRomanosAnterior)){
                        echo "<tr>";
                        if($temLetra){
                            echo "<th rowspan='2' colspan='2' valign='middle'>Nível</th>";
                        }else{
                            echo "<th rowspan='2' valign='middle'>Nível</th>";
                        }
                        echo "<th rowspan='2' valign='middle'>Faixa</th>";
                        echo "<th colspan='$numPadroes' valign='middle'>Padrão</th>";
                        echo "</tr><tr>";
                        for($a = 1;$a <= $numPadroes;$a++){
                            echo "<th>$a</th>";
                        }
                        echo "</tr>";
                        echo "<tr id='$cor'>";
                    }else{
                        echo "</tr>";
                        echo "<tr id='$cor'>";
                    }
                    
                    $faixaRomanosAnterior = $faixaRomanos;
                    echo "<td align='left'>$nn</td>";
                    if($temLetra){
                        echo "<td align='center'>$letra</td>";
                    }
                    echo "<td align='center'>$faixaRomanos</td>";
                    
                    echo "<td align='right'>";
                    
                    # Coloca o link de edição
                    if($editavel){
                        $link = new Link(formataMoeda($valor),'cadastroTabelaSalarial.php?fase=editar&pcv='.$idPlano.'&id='.$url);
                        $link->set_id("aLinkTabela");
                        $link->show();
                    }else{
                        echo formataMoeda($valor);
                    }
                        
                    echo "</td>";
                }else{
                    echo "<td align='right'>";
                    
                    # Coloca o link de edição
                    if($editavel){
                        $link = new Link(formataMoeda($valor),'cadastroTabelaSalarial.php?fase=editar&pcv='.$idPlano.'&id='.$url);
                        $link->set_id("aLinkTabela");
                        $link->show();
                    }else{
                        echo formataMoeda($valor);
                    }
                        
                    echo "</td>";
                }
                
            }
        }
        
        echo "</tr></table>";
    }
    
    ###########################################################
    
    
}