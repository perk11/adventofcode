package main

import (
	"fmt"
	"os"
	"strings"
)

type Design string
type Towel Design

var checkedDesigns map[Design]bool

func main() {
	filenames := []string{"testInput", "input"}
	for _, fileName := range filenames {
		fileContents, err := os.ReadFile(fileName)
		if err != nil {
			panic(err)
		}
		fmt.Println(fileName)
		lines := strings.Split(string(fileContents), "\n")
		availableTowelsStr := strings.Split(lines[0], ",")
		availableTowels := make(map[Towel]bool)
		for _, towelStr := range availableTowelsStr {
			towelStr = strings.TrimSpace(towelStr)
			availableTowels[Towel(towelStr)] = true
		}
		requiredDesigns := make([]Design, len(lines)-2)
		for lineNumber := 2; lineNumber < len(lines); lineNumber++ {
			requiredDesigns[lineNumber-2] = Design(lines[lineNumber])
		}
		total := 0
		checkedDesigns = make(map[Design]bool)
		for _, design := range requiredDesigns {
			if isDesignAvailable(design, availableTowels) {
				total++
			}
		}

		println(total)
	}
}
func isDesignAvailable(design Design, availableTowels map[Towel]bool) bool {
	if len(design) == 0 {
		return true
	}
	if result, ok := checkedDesigns[design]; ok {
		return result
	}
	possibleTowels := findPossibleNextTowels(design, availableTowels)
	if len(possibleTowels) == 0 {
		return false
	}
	for _, possibleTowel := range possibleTowels {
		designToCheck := design[len(possibleTowel):]
		if isDesignAvailable(designToCheck, availableTowels) {
			checkedDesigns[design] = true
			return true
		}
	}
	checkedDesigns[design] = false
	return false

}
func findPossibleNextTowels(design Design, availableTowels map[Towel]bool) []Towel {
	possibleTowels := make([]Towel, 0)
	for charIndex := 1; charIndex <= len(design); charIndex++ {
		towelPatternToSearch := Towel(design[0:charIndex])
		_, ok := availableTowels[towelPatternToSearch]
		if ok {
			possibleTowels = append(possibleTowels, towelPatternToSearch)
		}
	}
	return possibleTowels
}
