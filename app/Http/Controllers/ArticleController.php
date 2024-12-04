<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $articles=Article::with('categorie','forme')->get();
            return response()->json($articles,200);
            } catch (\Exception $e) {
            return response()->json("Sélection impossible {$e->getMessage()}");
            }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $article=new Article([
            "refEHK"=> $request->input('refEHK'),
            "designation"=> $request->input('designation'),
            "marque"=> $request->input('marque'),
            "refOrigine"=> $request->input('refOrigine'),
            "prixHT"=> $request->input('prixHT'),
            "accessoire"=> $request->input('accessoire'),
            "imageart"=> $request->input('imageart'),
            "categorieID"=> $request->input('categorieID'),
            "formeID"=> $request->input('formeID'),    
            ]);
            $article->save();
            return response()->json($article);
            
            } catch (\Exception $e) {
            return response()->json("insertion impossible {$e->getMessage()}");
            }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $article=Article::findOrFail($id);
            return response()->json($article);
        } catch (\Exception $e) {
            return response()->json("probleme de récupération des données {$e->getMessage()}");
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
        $article=Article::findorFail($id);
        $article->update($request->all());
        return response()->json($article);
        } catch (\Exception $e) {
        return response()->json("probleme de modification {$e->getMessage()}");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {

        $article=Article::findOrFail($id);

        $article->delete();
        return response()->json("article supprimée avec succes");
        } catch (\Exception $e) {
        return response()->json("probleme de suppression de article {$e->getMessage()}");
        }
    }

    public function showArticlesByCAT($idcat)
    {
        try {
            $articles= Article::where('categorieID', $idcat)->with('categorie','forme')->get();
            return response()->json($articles);
        } catch (\Exception $e) {
            return response()->json("Selection impossible {$e->getMessage()}");
        }
    }

    public function showArticlesByFOR($idfor)
    {
        try {
            $articles= Article::where('formeID', $idfor)->with('forme','categorie')->get();
            return response()->json($articles);
        } catch (\Exception $e) {
            return response()->json("Selection impossible {$e->getMessage()}");
        }
    }
    public function showArticlesByCATAndFOR($idcat,$idfor)
    {
        try {
            $articles= Article::where('formeID', $idfor)
            ->where('categorieID', $idcat)
            ->with('forme','categorie')->get();
            return response()->json($articles);
        } catch (\Exception $e) {
            return response()->json("Selection impossible {$e->getMessage()}");
        }
    }

    public function articlesPaginate()
    {
        try {
            $perPage = request()->input('pageSize', 5);
            $articles = Article::paginate($perPage); // Basic pagination test
    
            return response()->json([
                'products' => $articles->items(),
                'totalPages' => $articles->lastPage(),
            ]);
        } catch (\Exception $e) {
            return response()->json("Selection impossible {$e->getMessage()}");
        }
        
    }

}