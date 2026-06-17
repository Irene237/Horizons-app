class Product {
  final int id;
  final String name;
  final String reference;
  final int quantite;
  final int seuil;
  final double prix;

  Product({
    required this.id,
    required this.name,
    required this.reference,
    required this.quantite,
    required this.seuil,
    required this.prix,
  });

  factory Product.fromJson(Map<String, dynamic> json) {
    return Product(
      // On convertit tout proprement pour éviter les erreurs de type
      id: (json['id'] is int) ? json['id'] : int.tryParse(json['id'].toString()) ?? 0,
      
      name: json['name']?.toString() ?? '',
      
      reference: json['reference']?.toString() ?? '',
      
      // On convertit les chaînes (ex: "45") en entiers
      quantite: (json['stock_quantity'] != null) 
          ? int.tryParse(json['stock_quantity'].toString()) ?? 0 
          : 0,
          
      seuil: (json['alert_threshold'] != null) 
          ? int.tryParse(json['alert_threshold'].toString()) ?? 0 
          : 0,
          
      // On convertit les chaînes (ex: "3500.00") en nombres décimaux
      prix: (json['selling_price'] != null) 
          ? double.tryParse(json['selling_price'].toString()) ?? 0.0 
          : 0.0,
    );
  }

  bool get isCritical => quantite <= seuil;
}