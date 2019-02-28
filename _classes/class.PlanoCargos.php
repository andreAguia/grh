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
        
        # Pega o nome da tabela
        $dados = $this->get_dadosPlano($idPlano);
                
        # Inicia a Tabela
        echo "<table class='tabelaPadrao'>";
        echo '<caption>'.$dados[0].'</caption>';
        
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
    
    public function get_planoVigente($data = NULL,$idServidor = NULL){
    /**
     * Retorna o id do cadastro de plano de cargos que estava vigente na data indicada para o servidor indicado
     * 
     * @param $data       date    NULL A data desejada
     * @param $idServidor integer NULL O id do servidor analizado
     * 
     * @syntax $plano->get_planoVigente($data);
     *
     * @Obs O id Servidor é necessário pois existem planos que só é válido para determinados cargos
     * @Obs Se a data estiver nulla será considerada a data atual
     * @Obs Utilizada na rotina de enquadramento e progressão para saber em qual plano o servidor foi enquadrado ou progredido
     */
    
        # Conecta
        $pessoal = new Pessoal();
        
        # Verifica se a data é nula
        if(is_null($data)){
            $data = date("d/m/Y");
        }
        
        # Verifica se servidor é professor ou adm e Tec
        $tipo = $pessoal->get_cargoTipo($idServidor);
        
        # Pega os projetos cadastrados
        $select = 'SELECT idPlano
                     FROM tbplano
                     WHERE dtVigencia <= "'.date_to_bd($data).'"
                     AND idPlano <> 6
                     AND (servidores = "Todos" OR servidores = "'.$tipo.'")
                     ORDER BY dtVigencia desc limit 1';
        
        
        $row = $pessoal->select($select,false);
        return $row[0];
    }
    
    ###########################################################
    
    public function get_salarioClasse($idPlano = NULL, $classe = NULL){
    /**
     * Retorna o salário cadastrado da classe do idPlano fornecido
     * 
     * @param $idPlano integer NULL O id do plano
     * @param $classe  texto   NULL A classe do salário
     * 
     * @syntax $plano->get_salarioClasse($idPlano, $classe);
     */
    
        # Pega os projetos cadastrados
        $select = 'SELECT valor
                     FROM tbclasse
                     WHERE faixa = "'.$classe.'"
                     AND idPlano = "'.$idPlano.'"';
        
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,false);
        return $row[0];
    }
    
    ###########################################################
    
    public function get_idClasse($idPlano = NULL, $classe = NULL){
    /**
     * Retorna o salário cadastrado da classe do idPlano fornecido
     * 
     * @param $idPlano integer NULL O id do plano
     * @param $classe  texto   NULL A classe do salário
     * 
     * @syntax $plano->get_salarioClasse($idPlano, $classe);
     */
    
        # Pega os projetos cadastrados
        $select = 'SELECT idClasse
                     FROM tbclasse
                     WHERE faixa = "'.$classe.'"
                     AND idPlano = "'.$idPlano.'"';
        
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,false);
        return $row[0];
    }
    
    ###########################################################
    
    public function evibeValor($idClasse){
    /**
     * Exive informação da faixa, valor e plano referente ao idClasse para ser exibido na rotina de cadastro de enquadramento & progressão
     * 
     * @param $classe  texto   NULL A classe do salário
     * 
     * @syntax $plano->evibeValor($idclasse);
     */
    
        # Pega os projetos cadastrados
        $select = 'SELECT faixa,
                          valor,
                          tbplano.numdecreto
                     FROM tbclasse LEFT JOIN tbplano USING (idPlano)
                     WHERE idClasse = "'.$idClasse.'"';
        
        
        $pessoal = new Pessoal();
        $row = $pessoal->select($select,false);
        
        p($row[0]." - ".$row[1],"pprogressao1");
        p($row[2],"pprogressao2");
    }
    
    ###########################################################
    
    
}