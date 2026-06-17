import 'package:flutter/material.dart';
import '../models/product.dart';
import '../services/product_service.dart';

class ProductListScreen extends StatefulWidget {
  const ProductListScreen({super.key});

  @override
  State<ProductListScreen> createState() => _ProductListScreenState();
}

class _ProductListScreenState extends State<ProductListScreen> {
  final ProductService _productService = ProductService();
  List<Product> _allProducts = [];
  List<Product> _filteredProducts = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _loadProducts();
  }

  Future<void> _loadProducts() async {
    try {
      final products = await _productService.getProducts();
      setState(() {
        _allProducts = products;
        _filteredProducts = products;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      // Optionnel : afficher un snackbar d'erreur ici
    }
  }

  void _runFilter(String enteredKeyword) {
    List<Product> results = [];
    if (enteredKeyword.isEmpty) {
      results = _allProducts;
    } else {
      results = _allProducts.where((p) => 
        // Utilisation de .name et .reference (nouveaux noms)
        p.name.toLowerCase().contains(enteredKeyword.toLowerCase()) || 
        p.reference.toLowerCase().contains(enteredKeyword.toLowerCase())
      ).toList();
    }
    setState(() => _filteredProducts = results);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("Stock Produits")),
      body: _isLoading 
        ? const Center(child: CircularProgressIndicator())
        : Column(
            children: [
              Padding(
                padding: const EdgeInsets.all(12.0),
                child: TextField(
                  onChanged: (value) => _runFilter(value),
                  decoration: const InputDecoration(labelText: 'Rechercher...', prefixIcon: Icon(Icons.search)),
                ),
              ),
              Expanded(
                child: ListView.builder(
                  itemCount: _filteredProducts.length,
                  itemBuilder: (context, index) {
                    final product = _filteredProducts[index];
                    return Card(
                      color: product.isCritical ? Colors.red[50] : Colors.white,
                      child: ListTile(
                        // Utilisation de .name
                        title: Text(product.name, style: TextStyle(color: product.isCritical ? Colors.red : Colors.black)),
                        subtitle: Text("Stock: ${product.quantite} | Prix: ${product.prix} FCFA"),
                        trailing: product.isCritical ? const Icon(Icons.warning, color: Colors.red) : null,
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
    );
  }
}