<?php
/**
 * Sistema GRH
 * 
 * Relatório
 *   
 * By Alat
 */

# Servidor logado 
$idUsuario = NULL;

# Configuração
include ("../grhSistema/_config.php");

# Permissão de Acesso
$acesso = Verifica::acesso($idUsuario,2);

if($acesso)
{    
    # Conecta ao Banco de Dados
    $servidor = new Pessoal();

    # Começa uma nova página
    $page = new Page();			
    $page->iniciaPagina();
    
    # Pega os parâmetros dos relatórios
    $parametroNivel = post('parametroNivel',get_session('parametroNivel','Elementar'));

    ######
    
    $select ='SELECT tbservidor.idFuncional,
                     tbpessoa.nome,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     tbservidor.idServidor,
                     CONCAT("Nível ",tbtipocargo.nivel),
                     tbservidor.idServidor
                FROM tbservidor LEFT JOIN tbpessoa USING (idPessoa)
                                LEFT JOIN tbperfil USING (idPerfil)
                                LEFT JOIN tbcargo USING (idCargo)
                                     JOIN tbtipocargo USING (idTipoCargo) 
               WHERE tbservidor.situacao = 1
                 AND tbtipocargo.tipo = "Adm/Tec"
                 AND (idPerfil = 1 OR idPerfil = 4)
                 AND tbtipocargo.nivel = "'.$parametroNivel.'"
            ORDER BY tbtipocargo.nivel, tbpessoa.nome';

    $result = $servidor->select($select);

    $relatorio = new Relatorio();
    $relatorio->set_titulo('Relatório de Administrativos e Técnicos Ativos');
    $relatorio->set_tituloLinha2('Com a Última Progressão / Enquadramento');
    $relatorio->set_subtitulo('Agrupados por Escolaridade do Cargo - Ordenados pelo Nome');
    $relatorio->set_label(array('IdFuncional','Nome','Cargo','Lotação','Salário Atual','Data Inicial',"Nível"));
    #$relatorio->set_width(array(10,30,30,0,10,10,10));
    $relatorio->set_align(array("center","left","left","left"));
    $relatorio->set_funcao(array(NULL,NULL,NULL,NULL,"exibeDadosSalarioAtual"));
    
    $relatorio->set_classe(array(NULL,NULL,"pessoal","pessoal",NULL,"Progressao"));
    $relatorio->set_metodo(array(NULL,NULL,"get_Cargo","get_Lotacao",NULL,"get_dtInicialAtual"));
    
    $relatorio->set_bordaInterna(TRUE);
    $relatorio->set_conteudo($result);
    $relatorio->set_numGrupo(6);

    $relatorio->set_formCampos(array(
                               array ('nome' => 'parametroNivel',
                                      'label' => 'Nivel:',
                                      'tipo' => 'combo',
                                      'array' => array("Elementar","Fundamental","Médio","Superior"),
                                      'size' => 30,
                                      'col' => 4,
                                      'padrao' => $parametroNivel,
                                      'title' => 'Nível de Escolaridade do Cargo',
                                      'onChange' => 'formPadrao.submit();',
                                      'linha' => 1)));

    $relatorio->set_formFocus('nivel');
    $relatorio->set_formLink('?');
        
    $relatorio->show();

    $page->terminaPagina();
}