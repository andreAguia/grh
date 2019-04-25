<?php
class Procedimento{
 /**
  * Abriga as várias rotina do Sistema de Procedimentos
  * 
  * @author André Águia (Alat) - alataguia@gmail.com
  * 
  */
    
    ###########################################################
    
    /**
    * Método Construtor
    */
    public function __construct(){
        
    }
    
    ###########################################################
    
    public static function get_dadosProcedimento($idProcedimento = NULL){
    /**
     * Retorna um array com as informações da nota
     * 
     * @param $idNota integer NULL o idNota
     * 
     * @syntax $projeto->get_dadosNota([$idNota]);  
     */
    
        # Pega os procedimentos
        $select = 'SELECT idProcedimento,
                          categoria,
                          titulo,
                          texto,
                          numOrdem
                     FROM tbprocedimento
                    WHERE idProcedimento = '.$idProcedimento;
        
        $intra = new Intra();
        $row = $intra->select($select,false);
        return $row;
    }
               
    ###########################################################
    
    public static function get_numeroNotas($idCaderno){
    /**
     * Retorna um inteiro com o número de notas de um caderno
     * 
     * @param $idCaderno integer NULL o idCaderno 
     * 
     * @syntax $projeto->get_numeroNotas([$idCaderno]);  
     */
    
        # Pega os projetos cadastrados
        $select = 'SELECT idNota
                     FROM tbprojetonota
                    WHERE idCaderno = '.$idCaderno;
        
        $intra = new Intra();
        $numNotas = $intra->count($select);
        return $numNotas;
    }
    
    ##########################################################
    
    public static function menu(){
    /**
    * Exibe o menu de Categoria.
    * 
    * @syntax Procedimento::menuCategoria();
    */    
   
        # Acessa o banco de dados
        $pessoal = new Pessoal();
        
        # Pega os projetos cadastrados
        $select = 'SELECT categoria
                     FROM tbprocedimento
                  ORDER BY categoria';
        
        $categorias = $pessoal->select($select);
        $quantidade = $pessoal->count($select);
        
        # Inicia o menu
        $menu1 = new Menu();
        $menu1->add_item('titulo1','Categorias');
        $menu1->add_item('sublink','+ Novo Procedimento','?fase=editaProcedimento');
                
        # Verifica se tem cadernos
        if($quantidade > 0){
            # Percorre o array 
            foreach ($categorias as $valor){
                #S$numNotas = $projeto->get_numeroNotas($valor[0]);
                $texto = $valor[1]." <span id='numProjeto'>$numNotas</span>";                

                # Marca o item que está sendo editado
                if($idCaderno == $valor[0]){
                    $menu1->add_item('titulo2',"<i class='fi-book'></i><b> ".$texto."</b>",'?fase=dadosCaderno&idCaderno='.$valor[0],$valor[2]);

                    # Pega as notas
                    $select = 'SELECT idNota,
                                      titulo,
                                      descricao
                                 FROM tbprojetonota
                                WHERE idcaderno = '.$valor[0].' ORDER BY numOrdem,titulo';

                    # Acessa o banco
                    $notas = $intra->select($select);
                    $numNotas = $intra->count($select);

                    # Incluir nota
                    #$menu1->add_item('sublink','+ Nova Nota','?fase=notaNova');

                    # Percorre as notas 
                    foreach($notas as $tituloNotas){
                        if($idNota == $tituloNotas[0]){
                            $menu1->add_item('link',"<i class='fi-page'></i><b> ".$tituloNotas[1].'</b>','?fase=caderno&idNota='.$tituloNotas[0],$tituloNotas[2]);
                        }else{
                            $menu1->add_item('link',"<i class='fi-page'></i> ".$tituloNotas[1],'?fase=caderno&idNota='.$tituloNotas[0],$tituloNotas[2]);
                        }
                    }
                }else{
                    $menu1->add_item('titulo2',"<i class='fi-book'></i> ".$texto,'?fase=dadosCaderno&idCaderno='.$valor[0],$valor[2]);
                }

            }           
                        
        }
        $menu1->show();
    }

    ##########################################################
}