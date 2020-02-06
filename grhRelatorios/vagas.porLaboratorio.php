<?php
/**
 * Relatório
 *    
 * By Alat
 */

# Inicia as variáveis que receberão as sessions
$idUsuario = NULL;              # Servidor logado

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

# Pega os parâmetros
$parametroCentro = get_session('parametroCentro');

if($acesso){
    
    # Conecta ao Banco de Dados
    $pessoal = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();

    ######   
    
    # Título & Subtitulo
    $subTitulo = $parametroCentro;
    $titulo = "Vagas por Laboratório";

    # Pega os dados
    $select = 'SELECT tbcargo.nome,
                      idVaga,
                      idVaga,
                      idVaga,
                      idVaga,
                      (SELECT idLotacao 
                         FROM tbvagahistorico JOIN tbconcurso USING (idConcurso) 
                        WHERE idVaga = con1.idVaga 
                     ORDER BY tbconcurso.dtPublicacaoEdital desc LIMIT 1)
                 FROM tbvaga con1 LEFT JOIN tbcargo USING (idCargo)
                WHERE TRUE ';
    
    # parametroCentro
    if(!vazio($parametroCentro)){
        $select .= "AND centro = '$parametroCentro'";
    }

    $select .= ' ORDER BY centro, 6, idCargo desc';

    $resumo = $pessoal->select($select);

    # Monta o Relatório
    $relatorio = new Relatorio();
    $relatorio->set_conteudo($resumo);
    
    $relatorio->set_label(array("Cargo","Status","Último Ocupante","Obs","Num. de Concursos","Origem"));
    $relatorio->set_width(array(20,10,30,25));
    $relatorio->set_align(array("center"));
    
    #$relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"date_to_php"));
    #$relatorio->set_width(array(5,5,5,20,5,20,15,15,5));

    $relatorio->set_classe(array(NULL,"Vaga","Vaga","Vaga","Vaga","Pessoal"));
    $relatorio->set_metodo(array(NULL,"get_status","get_servidorOcupante","get_obsOcupante","get_numConcursoVaga","get_nomelotacao2"));

    $relatorio->set_titulo($titulo);
    $relatorio->set_subtitulo($subTitulo);
    
    #$relatorio->set_numeroOrdem(TRUE);
    #$relatorio->set_numeroOrdemTipo('d');
    $relatorio->set_bordaInterna(TRUE);
    $relatorio->set_numGrupo(5);
    
    $relatorio->show();

    $page->terminaPagina();
}