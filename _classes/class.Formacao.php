<?php
class Formacao{
 /**
  * Abriga as várias rotina do Cadastro de Formação Escolar do servidor
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
    
    function get_curso($idFormacao){
        
    /**
     * fornece Detalhes do curso
     */
        
        # Pega os dados
        $select="SELECT *
                   FROM tbformacao
                  WHERE idFormacao = $idFormacao";
        
        $pessoal = new Pessoal();
        $dados = $pessoal->select($select,FALSE);
        
        $retorno = NULL;
        
        # Escolaridade
        #$retorno = '<span title="Nível do Curso" id="orgaoCedido">['.$pessoal->get_escolaridade($dados['idEscolaridade']).']</span><br/>';
        
        # Nome do Curso
        $retorno .= $dados['habilitacao'];
        
        # Ano de Término
        if(!vazio($dados['anoTerm'])){
            $retorno .= '<br/><span title="Ano de Conclusão" id="orgaoCedido">['.$dados['anoTerm'].']</span)';
        }
        
        return $retorno;
    }
    
    ###########################################################
    
    function get_escolaridade($idServidor){
        /**
         * Fornece a escolaridade de um servidor seja pelo cargo, seja pelo cadastro de formação. o que tiver maior escolaridade
         */
        
        # inicia as variáveis
        $idEscolaridade = NULL;
        $valores = array();
        
        # Conecta ao banco de dados
        $pessoal = new Pessoal();
        
        # PEga o idPessoa desse servidor
        $idPessoa = $pessoal->get_idPessoa($idServidor);
        
        # Pega o id cargo do servidor
        $idCargo = $pessoal->get_idCargo($idServidor);
        
        # Pega o cargo específico
        $idTipoCargo = $pessoal->get_idTipoCargo($idCargo);
        
        # Pega a escolaridade do cargo
        switch ($idTipoCargo){
            
            # Professorea
            case 1:
            case 2:
                $idEscolaridade = 11;
                break;
            
            # Profissional de Nível Superior
            case 3:
                $idEscolaridade = 8;
                break;
            
            # Profissional de Nível Médio
            case 4:
                $idEscolaridade = 6;
                break;
            
            # Profissional de Nível Fundamental
            case 5:
                $idEscolaridade = 4;
                break;
            
            # Profissional de Nível Elementar
            case 6:
                $idEscolaridade = 2;
                break;
            
            default:
                $idEscolaridade = 0;
                break;
        }
        
        # Joga o primeiro valor no array de comparação
        $valores[] = $idEscolaridade;
        
        # Pega a escolaridade da tabela formação
        $select = "SELECT idEscolaridade FROM tbformacao WHERE idEscolaridade <> 12 AND idPessoa = $idPessoa ORDER BY idEscolaridade desc LIMIT 1";
        $dados = $pessoal->select($select,FALSE);
        
        # Joga o segundo valor no array de comparação
        $valores[] = $dados[0];
                
        # Pega a maior escolaridade
        $maior = maiorValor($valores);
        
        # Retorna a maior escolaridade registrada
        return $maior;
    }
        
}