##
# business.rb
# holds/normalizes business information
# @author rbacon
# @date 20091006

class Business

    # Soundex algorithm from snippets.dzone.com/tag/soundex
    SoundexChars = 'BPFVCSKGJQXZDTLMNR'
    SoundexNums  = '111122222222334556'
    SoundexCharsEx = '^' + SoundexChars
    SoundexCharsDel = '^A-Z'

    StreetAbbrevs = {
        "STREET" => "ST",
        "DRIVE" => "DR", 
        "ROAD" => "RD",
        "AVENUE" => "AV",
        "AVE" => "AV",
        "BOULEVARD" => "BLVD",
        "WAY" => "WY",
        "LANE" => "LN",
        "PARKWAY" => "PKWY",
        "SUITE" => "STE"  # for fixing suite
    }
    
    
    attr_accessor :id, :city, :state, :zip
    attr_accessor :phone, :categories, :keywords

    attr_reader :name, :aka, :address


    
    def initialize(params=nil)
        @categories = []
        @keywords = []    
    
        return if params.nil?
        
        # call assignment method if it exists for param
        params.each do |key, val|
            assignment_method = (key.to_s + '=').to_sym
            if self.respond_to? assignment_method
                self.send assignment_method, val
            end
        end
        # it appears you have to explicitly call self to get the methods to work
 #       self.id = params[:id] if !params[:id].nil?
 #       self.name = params[:name] if !params[:name].nil?
 #       self.aka = params[:aka] if !params[:aka].nil?
 #       self.address = params[:address] if !params[:address].nil?
 #       self.city = params[:city] if !params[:city].nil?
 #       self.state = params[:state] if !params[:state].nil?
 #       self.zip = params[:zip] if !params[:zip].nil?
 #       self.phone = params[:phone] if !params[:phone].nil?
    end
    
    def name=(name)
        @name = name
        @normalized_name = soundex(normalize(name), false)
    end

    def aka=(aka)
        @aka = aka
        @normalized_aka = soundex(normalize(aka), false)
    end
        
    def address=(address)
        @address = address
        @normalized_address = normalize_address(address)
    end
    
    def ==(other_business)
        (normalized_address == other_business.normalized_address) &&
            similar_name(other_business.normalized_name)
    end

    
    # need methods to compare businesses to see if they are likely "dupes"
    protected
    
    attr_reader :normalized_name, :normalized_address
    
    # a name is similar if one of the business's "soundexes" is a prefix of the other one.
    def similar_name(other_norm_name)
        (normalized_name.start_with?(other_norm_name) || other_norm_name.start_with?(normalized_name))
    end
    
    private
    
    def normalize(string)
        return '' if string.nil?
        copy = string.upcase.strip
        copy.gsub('&', ' AND ').gsub(/[\/,]/,' ').gsub(/[^A-Z0-9 ]/,'').gsub(/ +/,' ')
    end
   
     
    # normalize address for comparison
    def normalize_address(address)
        normalize(address).split(' ').map { |word| StreetAbbrevs[word] || word }.join(' ')
    end
    
    # desc: http://en.wikipedia.org/wiki/Soundex
    def soundex(string, census = true)
        str = string.upcase.delete(SoundexCharsDel).squeeze

        str[0 .. 0] + str[1 .. -1].
            delete(SoundexCharsEx).
            tr(SoundexChars, SoundexNums)[0 .. (census ? 2 : -1)].
            ljust(2, '0') rescue ''
    end
end
