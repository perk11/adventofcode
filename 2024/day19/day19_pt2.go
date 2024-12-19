package main

import (
	"fmt"
	"os"
	"strings"
)

type Design string
type Towel Design

var checkedDesigns map[Design]int

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
		checkedDesigns = make(map[Design]int)
		for _, design := range requiredDesigns {
			total += countDesigns(design, availableTowels)
		}

		println(total)
	}
}

func countDesigns(design Design, availableTowels map[Towel]bool) int {
	if len(design) == 0 {
		return 1
	}
	if result, ok := checkedDesigns[design]; ok {
		return result
	}
	possibleTowels := findPossibleNextTowels(design, availableTowels)
	if len(possibleTowels) == 0 {
		return 0
	}
	designsAvailable := 0
	for _, possibleTowel := range possibleTowels {
		designToCheck := design[len(possibleTowel):]
		designsAvailable += countDesigns(designToCheck, availableTowels)
	}
	checkedDesigns[design] = designsAvailable
	return designsAvailable

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
